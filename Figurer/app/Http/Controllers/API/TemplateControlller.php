<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\cv_template_data;

class TemplateControlller extends Controller
{   
    public function getAllOwned(Request $request)
    {
        $user = auth()->user();
        $ownedTemplate = $user->owned_template;

        if (!is_array($ownedTemplate)) {
            $ownedTemplate = json_decode($ownedTemplate, true);
        }

        $templates = cv_template_data::whereIn('unique_cv_id', $ownedTemplate)->get();

        return response()->json($templates);
    }

    public function getByID(Request $request, $id)
    {
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
            'id_number' => 'required',
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

    public function patch(Request $request, $id)
    {
        $template = cv_template_data::findOrFail($id);

        $data = $request->validate([
            'name' => 'required',
            'id_number' => 'required',
            'price' => 'required',
            'template-link' => 'required',
            'template-preview' => 'required',
        ]);

        $template->update($data);

        return response()->json($template, 200);
    }

    public function delete(Request $request, $id)
    {
        $template = cv_template_data::findOrFail($id);
        $template->delete();

        return response()->json(null, 204);
    }
}