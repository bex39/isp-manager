<?php

namespace App\Http\Controllers;

use App\Models\AcsConfigTemplate;
use App\Models\ONT;
use App\Models\AcsBulkOperation;
use Illuminate\Http\Request;

class AcsTemplateController extends Controller
{
    /**
     * Display a listing of templates
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $type = $request->input('type');

        $query = AcsConfigTemplate::with('creator');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($type) {
            $query->where('type', $type);
        }

        $templates = $query->latest()->paginate(20)->withQueryString();

        // Statistics
        $stats = [
            'total' => AcsConfigTemplate::count(),
            'wifi' => AcsConfigTemplate::where('type', 'wifi')->count(),
            'vlan' => AcsConfigTemplate::where('type', 'vlan')->count(),
            'service_profile' => AcsConfigTemplate::where('type', 'service_profile')->count(),
            'custom' => AcsConfigTemplate::where('type', 'custom')->count(),
        ];

        return view('acs.templates.index', compact('templates', 'stats'));
    }

    /**
     * Show the form for creating a new template
     */
    public function create()
    {
        return view('acs.templates.create');
    }

    /**
     * Store a newly created template
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100|unique:acs_config_templates,code',
            'type' => 'required|in:wifi,vlan,port,service_profile,custom',
            'description' => 'nullable|string',
            'parameters' => 'required|array',
            'is_default' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['is_default'] = $request->has('is_default');
        $validated['is_active'] = $request->has('is_active') ? true : false;

        // If set as default, unset other defaults of same type
        if ($validated['is_default']) {
            AcsConfigTemplate::where('type', $validated['type'])
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        $template = AcsConfigTemplate::create($validated);

        return redirect()->route('acs.templates.index')
            ->with('success', 'Template created successfully!');
    }

    /**
     * Display the specified template
     */
    public function show(AcsConfigTemplate $template)
    {
        $template->load('creator');

        // Get usage statistics
        $stats = [
            'total_applications' => $template->histories()->count(),
            'successful' => $template->histories()->where('status', 'success')->count(),
            'failed' => $template->histories()->where('status', 'failed')->count(),
            'devices_using' => $template->onts()->count(),
        ];

        // Recent applications
        $recentApplications = $template->histories()
            ->with(['ont', 'executor'])
            ->latest()
            ->take(10)
            ->get();

        return view('acs.templates.show', compact('template', 'stats', 'recentApplications'));
    }

    /**
     * Show the form for editing the specified template
     */
    public function edit(AcsConfigTemplate $template)
    {
        return view('acs.templates.edit', compact('template'));
    }

    /**
     * Update the specified template
     */
    public function update(Request $request, AcsConfigTemplate $template)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100|unique:acs_config_templates,code,' . $template->id,
            'type' => 'required|in:wifi,vlan,port,service_profile,custom',
            'description' => 'nullable|string',
            'parameters' => 'required|array',
            'is_default' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_default'] = $request->has('is_default');
        $validated['is_active'] = $request->has('is_active') ? true : false;

        // If set as default, unset other defaults of same type
        if ($validated['is_default']) {
            AcsConfigTemplate::where('type', $validated['type'])
                ->where('is_default', true)
                ->where('id', '!=', $template->id)
                ->update(['is_default' => false]);
        }

        $template->update($validated);

        return redirect()->route('acs.templates.index')
            ->with('success', 'Template updated successfully!');
    }

    /**
     * Remove the specified template
     */
    public function destroy(AcsConfigTemplate $template)
    {
        // Check if template is in use
        $inUse = $template->onts()->count();

        if ($inUse > 0) {
            return back()->with('error', "Cannot delete template. It is currently used by {$inUse} device(s).");
        }

        $template->delete();

        return redirect()->route('acs.templates.index')
            ->with('success', 'Template deleted successfully!');
    }

    /**
     * Duplicate template
     */
    public function duplicate(AcsConfigTemplate $template)
    {
        $newTemplate = $template->replicate();
        $newTemplate->name = $template->name . ' (Copy)';
        $newTemplate->code = $template->code . '_copy_' . time();
        $newTemplate->is_default = false;
        $newTemplate->created_by = auth()->id();
        $newTemplate->save();

        return redirect()->route('acs.templates.edit', $newTemplate)
            ->with('success', 'Template duplicated successfully!');
    }

    /**
     * Set template as default
     */
    public function setDefault(AcsConfigTemplate $template)
    {
        // Unset other defaults of same type
        AcsConfigTemplate::where('type', $template->type)
            ->where('is_default', true)
            ->update(['is_default' => false]);

        // Set this as default
        $template->update(['is_default' => true]);

        return back()->with('success', 'Template set as default!');
    }

    /**
     * Apply template to single device
     */
    public function apply(Request $request, AcsConfigTemplate $template)
    {
        $validated = $request->validate([
            'ont_id' => 'required|exists:onts,id',
        ]);

        $ont = ONT::find($validated['ont_id']);

        try {
            // Log config change
            $ont->logConfigChange(
                'apply_template',
                [
                    'template_id' => $template->id,
                    'template_name' => $template->name,
                    'parameters' => $template->parameters,
                ],
                'pending',
                auth()->id()
            );

            // Add to provisioning queue
            $ont->queueForProvisioning('apply_template', 'normal', [
                'template_id' => $template->id,
                'parameters' => $template->parameters,
            ]);

            return back()->with('success', "Template '{$template->name}' queued for application!");

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to apply template: ' . $e->getMessage());
        }
    }

    /**
     * Apply template to multiple devices (bulk)
     */
    public function bulkApply(Request $request, AcsConfigTemplate $template)
    {
        $validated = $request->validate([
            'ont_ids' => 'required|array|min:1',
            'ont_ids.*' => 'exists:onts,id',
        ]);

        try {
            $ontIds = $validated['ont_ids'];

            // Create bulk operation
            $bulkOperation = AcsBulkOperation::create([
                'operation_name' => "Apply Template: {$template->name}",
                'operation_type' => 'apply_template',
                'target_filter' => ['ont_ids' => $ontIds],
                'parameters' => [
                    'template_id' => $template->id,
                    'template_parameters' => $template->parameters,
                ],
                'total_devices' => count($ontIds),
                'status' => 'pending',
                'created_by' => auth()->id(),
            ]);

            // Create detail records for each device
            foreach ($ontIds as $ontId) {
                $bulkOperation->details()->create([
                    'ont_id' => $ontId,
                    'status' => 'pending',
                ]);

                // Also add to provisioning queue
                $ont = ONT::find($ontId);
                $ont->queueForProvisioning('apply_template', 'normal', [
                    'template_id' => $template->id,
                    'parameters' => $template->parameters,
                    'bulk_operation_id' => $bulkOperation->id,
                ]);
            }

            return redirect()->route('acs.bulk.show', $bulkOperation)
                ->with('success', "Template will be applied to {$bulkOperation->total_devices} device(s)!");

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create bulk operation: ' . $e->getMessage());
        }
    }
}
