<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Template;
use App\Models\Inventory;
use Illuminate\Support\Facades\Log;
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

    public function getByID(Request $request, $id)
    {
        $template = Template::find($id);
        return response()->json($template);
    }

    public function getAllTemplates(Request $request)
    {
        $templates = Template::all();
        return response()->json($templates);
    }

    public function create(Request $request)
    {
        // Validate the request, including the images
        $data = $request->validate([
            'name' => 'required',
            'unique_cv_id' => 'required',
            'price' => 'required',
            'template-link' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:51200', // 50MB (51200 KB)
            'template-preview' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:51200', // 50MB
        ]);

        // Handle the 'template-link' image upload if present
        if ($request->hasFile('template-link')) {
            $templateLinkPath = $request->file('template-link')->store('public/template_links');
            $data['template-link'] = $templateLinkPath;
        }

        // Handle the 'template-preview' image upload if present
        if ($request->hasFile('template-preview')) {
            $templatePreviewPath = $request->file('template-preview')->store('public/template_previews');
            $data['template-preview'] = $templatePreviewPath;
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
            'name' => 'required',
            'price' => 'required',
            'template-link' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'template-preview' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Handle 'template-link' file upload
        if ($request->hasFile('template-link')) {
            $templateLinkPath = $request->file('template-link')->store('template_links', 'public');
            $data['template-link'] = $templateLinkPath; // Store the path in the $data array
        }

        // Handle 'template-preview' file upload
        if ($request->hasFile('template-preview')) {
            $templatePreviewPath = $request->file('template-preview')->store('template_previews', 'public');
            $data['template-preview'] = $templatePreviewPath; // Store the path in the $data array
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

}