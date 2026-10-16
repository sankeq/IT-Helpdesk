<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Department;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AssetController extends Controller
{
    /**
     * Display a listing of IT assets with search and filters.
     */
    public function index(Request $request)
    {
        if (! $request->user()->hasPermission('assets.view') && ! $request->user()->hasPermission('assets.manage')) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat inventaris aset TI.');
        }

        $query = Asset::with(['location', 'department', 'assignedTo'])
            ->withCount('tickets');

        // Search query
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('asset_tag', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        // Status filter
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Location filter
        if ($locationId = $request->input('location_id')) {
            $query->where('location_id', $locationId);
        }

        // Department filter
        if ($departmentId = $request->input('department_id')) {
            $query->where('department_id', $departmentId);
        }

        $assets = $query->orderBy('asset_tag', 'asc')->paginate(15)->withQueryString();

        $locations = Location::all();
        $departments = Department::all();

        // Calculate summary counts
        $totalCount = Asset::count();
        $activeCount = Asset::where('status', 'active')->count();
        $maintenanceCount = Asset::where('status', 'maintenance')->count();

        return view('assets.index', compact(
            'assets',
            'locations',
            'departments',
            'totalCount',
            'activeCount',
            'maintenanceCount'
        ));
    }

    /**
     * Show form for creating a new IT asset.
     */
    public function create(Request $request)
    {
        if (! $request->user()->hasPermission('assets.manage')) {
            abort(403, 'Hanya administrator atau supervisor yang dapat mendaftarkan aset baru.');
        }

        $locations = Location::all();
        $departments = Department::all();
        $users = User::where('is_active', true)->orderBy('name')->get();

        return view('assets.create', compact('locations', 'departments', 'users'));
    }

    /**
     * Store newly created asset in storage.
     */
    public function store(Request $request)
    {
        if (! $request->user()->hasPermission('assets.manage')) {
            abort(403, 'Hanya administrator atau supervisor yang dapat mendaftarkan aset baru.');
        }

        $validated = $request->validate([
            'asset_tag' => ['required', 'string', 'max:50', 'unique:assets,asset_tag'],
            'name' => ['required', 'string', 'max:150'],
            'category' => ['required', 'string', Rule::in(['hardware', 'software', 'network', 'peripheral'])],
            'model' => ['nullable', 'string', 'max:100'],
            'serial_number' => ['nullable', 'string', 'max:100', 'unique:assets,serial_number'],
            'status' => ['required', 'string', Rule::in(['active', 'maintenance', 'retired', 'disposed'])],
            'location_id' => ['nullable', 'exists:locations,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'assigned_to_user_id' => ['nullable', 'exists:users,id'],
            'purchase_date' => ['nullable', 'date'],
            'warranty_expiry' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $asset = Asset::create($validated);

        return redirect()->route('assets.show', $asset)
            ->with('success', "Aset TI [{$asset->asset_tag}] berhasil didaftarkan ke inventaris.");
    }

    /**
     * Display the specified asset and its complete ticket incident history.
     */
    public function show(Request $request, Asset $asset)
    {
        if (! $request->user()->hasPermission('assets.view') && ! $request->user()->hasPermission('assets.manage')) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat rincian aset TI.');
        }

        $asset->load(['location', 'department', 'assignedTo']);

        // Historical tickets for this asset
        $tickets = $asset->tickets()
            ->with(['requester', 'assignee', 'category'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('assets.show', compact('asset', 'tickets'));
    }

    /**
     * Show form for editing an asset.
     */
    public function edit(Request $request, Asset $asset)
    {
        if (! $request->user()->hasPermission('assets.manage')) {
            abort(403, 'Hanya administrator atau supervisor yang dapat mengubah data aset.');
        }

        $locations = Location::all();
        $departments = Department::all();
        $users = User::where('is_active', true)->orderBy('name')->get();

        return view('assets.edit', compact('asset', 'locations', 'departments', 'users'));
    }

    /**
     * Update asset data in storage.
     */
    public function update(Request $request, Asset $asset)
    {
        if (! $request->user()->hasPermission('assets.manage')) {
            abort(403, 'Hanya administrator atau supervisor yang dapat mengubah data aset.');
        }

        $validated = $request->validate([
            'asset_tag' => ['required', 'string', 'max:50', Rule::unique('assets', 'asset_tag')->ignore($asset->id)],
            'name' => ['required', 'string', 'max:150'],
            'category' => ['required', 'string', Rule::in(['hardware', 'software', 'network', 'peripheral'])],
            'model' => ['nullable', 'string', 'max:100'],
            'serial_number' => ['nullable', 'string', 'max:100', Rule::unique('assets', 'serial_number')->ignore($asset->id)],
            'status' => ['required', 'string', Rule::in(['active', 'maintenance', 'retired', 'disposed'])],
            'location_id' => ['nullable', 'exists:locations,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'assigned_to_user_id' => ['nullable', 'exists:users,id'],
            'purchase_date' => ['nullable', 'date'],
            'warranty_expiry' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $asset->update($validated);

        return redirect()->route('assets.show', $asset)
            ->with('success', "Informasi aset [{$asset->asset_tag}] berhasil diperbarui.");
    }

    /**
     * Remove asset or mark as retired if it has historical tickets.
     */
    public function destroy(Request $request, Asset $asset)
    {
        if (! $request->user()->hasPermission('assets.manage')) {
            abort(403, 'Hanya administrator atau supervisor yang dapat menghapus aset.');
        }

        if ($asset->tickets()->exists()) {
            $asset->update(['status' => 'retired']);

            return redirect()->route('assets.index')
                ->with('warning', "Aset [{$asset->asset_tag}] memiliki riwayat tiket terkait, sehingga statusnya dialihkan ke 'Retired' (tidak dihapus permanen).");
        }

        $tag = $asset->asset_tag;
        $asset->delete();

        return redirect()->route('assets.index')
            ->with('success', "Aset [{$tag}] berhasil dihapus dari inventaris.");
    }
}
