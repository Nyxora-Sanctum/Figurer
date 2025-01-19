<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\cv_template_data;

class TemplateController extends Controller
{   
    public function getAllOwned(Request $request)
    {
        \Log::debug('hh');
        $user = auth()->user();
        $ownedTemplate = $user->owned_template;
        \Log::debug($ownedTemplate);

        if (!is_array($ownedTemplate)) {
            $ownedTemplate = json_decode($ownedTemplate, true);
        }

        $templates = cv_template_data::whereIn('unique_cv_id', $ownedTemplate)->get();

        return response()->json($templates);
    }

    public function getByID(Request $request, $id)
    {
        \Log::debug('get cv by id');
        $template = cv_template_data::find($id);
        return response()->json($template);
    }

    public function getAllTemplates(Request $request)
    {
        $templates = cv_template_data::all();
        return response()->json($templates);
    }

    public function create(Request $request)
    {
        // Validate the request, including the images
        $data = $request->validate([
            'name' => 'required',
            'unique_cv_id' => 'required',
            'price' => 'required',
            'template-link' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'template-preview' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Handle the 'template-link' image upload if present
        if ($request->hasFile('template-link')) {
            $templateLinkPath = $request->file('template-link')->store('template_links', 'public');
            $data['template-link'] = $templateLinkPath;
        }

        // Handle the 'template-preview' image upload if present
        if ($request->hasFile('template-preview')) {
            $templatePreviewPath = $request->file('template-preview')->store('template_previews', 'public');
            $data['template-preview'] = $templatePreviewPath;
        }

        // Create the new template entry in the database with the uploaded image paths
        $template = cv_template_data::create($data);

        return response()->json($template, 201);
    }

    public function patch(Request $request, $unique_cv_id)
    {
        // Find the template record using the unique_cv_id
        $template = cv_template_data::where('unique_cv_id', $unique_cv_id)->firstOrFail();

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
        $template = cv_template_data::where('unique_cv_id', $unique_cv_id)->firstOrFail();

        // Delete the template
        $template->delete();

        // Return a 204 No Content response
        return response()->json(null, 204);
    }

}