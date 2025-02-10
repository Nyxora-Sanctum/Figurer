<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Template;
use App\Models\Inventory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Arr;


class TemplateController extends Controller
{   
    public function useTemplate(Request $request, $id)
    {
        $uid = auth()->user()->id;
        $template = Template::where('unique_cv_id', $id)->first();
        $inventory = Inventory::where('id', operator: $uid)->first();
        $available_items = Arr::flatten(json_decode($inventory->available_items, true));
        $used_items = json_decode($inventory->used_items, true);
        log::info($available_items);

        if (!$template) {
            return response()->json(['message' => 'Template not found'], 404);
        }

        if (!is_array($used_items)) {
            $used_items = [];
        }

        if ((in_array($template->unique_cv_id, $available_items))) {
            // Add the template to used_items
            $used_items['used_items'][] = $template->unique_cv_id;
            $inventory->used_items = json_encode($used_items);
            $inventory->save();

            $available_items = json_decode($inventory->available_items, true);
            $available_items['available_items'] = array_diff($available_items['available_items'], [$template->unique_cv_id]);
            $inventory->available_items = json_encode($available_items);
            $inventory->save();
            

            return response()->json(['message' => 'Template used successfully']);
        } else {
            return response()->json(['message' => 'Template already used'], 403);
        }
    }
    
    public function getAllOwned(Request $request)
    {
        $uid = auth()->user()->id;
        $inventory = Inventory::where('id', $uid)->first();

        if (!$inventory) {
            return response()->json(['message' => 'Inventory not found'], 404);
        }

        $ownedTemplate = $inventory->available_items;

        Log::info($ownedTemplate);
        if (!is_array($ownedTemplate)) {
            $ownedTemplate = json_decode($ownedTemplate, true);
        }

        // Flatten the array if it's nested
        $ownedTemplate = Arr::flatten($ownedTemplate);

        $templates = Template::whereIn('unique_cv_id', $ownedTemplate)->get();

        return response()->json($templates);
    }

    public function getAllUsed(Request $request)
    {
        $uid = auth()->user()->id;
        $usedItems = Inventory::where('id', $uid)->first()->used_items;

        Log::info($usedItems);

        if (!is_array($usedItems)) {
            $usedItems = json_decode($usedItems, true);
        }
        
        $usedItems = Arr::flatten($usedItems);

        $templates = Template::whereIn('unique_cv_id', $usedItems)->get();

        return response()->json($templates);
    }

    function getTotalTemplates(Request $request)
    {
        $templatesPerDay = Template::selectRaw('COUNT(*) as count, DAY(created_at) as day')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('count')
            ->toArray();

        $totalTemplates = array_sum($templatesPerDay);

        return response()->json([
            'per_day' => $templatesPerDay,
            'total' => $totalTemplates
        ]);
    }

    public function getByID(Request $request, $uniqueCvId)
    {
        $template = Template::where('unique_cv_id', $uniqueCvId)->first();
        return response()->json($template);
    }

     public function getOwnedByID(Request $request, $id)
    {
        $template = Template::find($id);
        return response()->json($template);
    }

    public function getAllTemplates(Request $request)
    {
        // Fetch all templates
        $templates = Template::all();

        // Add the full URL for template preview images
        $templates = $templates->map(function ($template) {
            if ($template->template_preview && Storage::disk('public')->exists($template->template_preview)) {
                $template->template_preview = Storage::url($template->template_preview);
            }
            return $template;
        });

        return response()->json($templates);
    }

    public function create(Request $request)
    {
        // Validate the request, including the images
        $data = $request->validate([
            'name' => 'required',
            'unique_cv_id' => 'required',
            'price' => 'required',
            'template-link' => 'required|mimes:html,php|max:51200',
            'template-preview' => 'required|image|mimes:jpeg,png,jpg|max:51200',
        ]);


        // Handle the 'template-link' image upload if present
        if ($request->hasFile('template-link')) {
            $templateLinkPath = $request->file('template-link')->store('template_links', 'public');
            $data['template-link'] = 'storage/' . $templateLinkPath;
        }

        // Handle the 'template-preview' image upload if present
        if ($request->hasFile('template-preview')) {
            $templatePreviewPath = $request->file('template-preview')->store('template_previews', 'public');
            $data['template-preview'] = 'storage/' . $templatePreviewPath;
        }

        // Create the new template entry in the database with the uploaded image paths
        $template = Template::create($data);

        return response()->json($template, 201);
    }

    public function patch(Request $request, $unique_cv_id)
    {
        // Find the template record using the unique_cv_id
        $template = Template::where('unique_cv_id', $unique_cv_id)->firstOrFail();

        // Validate incoming data
        $data = $request->validate([
            'name' => 'nullable',
            'price' => 'nullable',
            'template-link' => 'nullable|mimes:html,php|max:51200',
            'template-preview' => 'nullable|image|mimes:jpeg,png,jpg|max:51200',
        ]);

        // Handle 'template-link' file upload
        if ($request->hasFile('template-link')) {
            $templateLinkPath = $request->file('template-link')->store('template_links', 'public');
            $data['template-link'] = 'storage/' . $templateLinkPath; // Fix path storage consistency
        }

        // Handle 'template-preview' file upload
        if ($request->hasFile('template-preview')) {
            $templatePreviewPath = $request->file('template-preview')->store('template_previews', 'public');
            $data['template-preview'] = 'storage/' . $templatePreviewPath; // Fix path storage consistency
        }

        // Update the template record with validated data
        $template->update($data);

        return response()->json($template, 200);
    }


    public function delete(Request $request, $unique_cv_id)
    {
        // Find the template record using the unique_cv_id
        $template = Template::where('unique_cv_id', $unique_cv_id)->firstOrFail();

        // Delete the template
        $template->delete();

        // Return a 204 No Content response
        return response()->json(null, 204);
    }

    public function getInventoryByID(Request $request, $id)
    {
        $template = Inventory::find($id);

        if (!$template) {
            return response()->json(['error' => 'Inventory not found'], 404);
        }

        // Decode JSON fields properly
        $avail = json_decode($template->available_items, true)['available_items'] ?? [];
        $used = json_decode($template->used_items, true)['used_items'] ?? [];

        // Ensure they are arrays (even if empty)
        $avail = is_array($avail) ? $avail : [$avail];
        $used = is_array($used) ? $used : [$used];

        // Fetch templates using unique_cv_id
        $avail_template = Template::whereIn('unique_cv_id', $avail)->get();
        $user_template = Template::whereIn('unique_cv_id', $used)->get();

        return response()->json([
            'available' => $avail_template,
            'used' => $user_template
        ]);
    }

    public function deleteTemplateInventory(Request $request, $id, $unique_cv_id)
    {
        $inventory = Inventory::find($id);

        log::info($inventory);
        if (!$inventory) {
            return response()->json(['error' => 'Inventory not found'], 404);
        }

        $available_items = json_decode($inventory->available_items, true);

        if (!is_array($available_items)) {
            return response()->json(['error' => 'Invalid inventory data'], 400);
        }

        // Flatten the array properly
        $available_items = Arr::flatten($available_items);

        // Filter out the item with the given unique_cv_id
        $available_items = array_filter($available_items, function ($item) use ($unique_cv_id) {
            return isset($item['unique_cv_id']) && $item['unique_cv_id'] !== $unique_cv_id;
        });

        // Re-encode the available items and update the inventory
        $inventory->available_items = json_encode(array_values($available_items));
        $inventory->save();

        return response()->json(['message' => 'Item deleted successfully']);
    }

}