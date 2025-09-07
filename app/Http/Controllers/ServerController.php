<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Server;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreServerRequest;
use Illuminate\Http\JsonResponse;

class ServerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|JsonResponse
     */
    public function index(Request $request)
    {
        // Start with a base query
        $query = Server::query();

        // Filtering
        if ($request->has('provider') && $request->provider) {
            $query->where('provider', $request->input('provider'));
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->input('status'));
        }

        // Search
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->input('search') . '%')
                  ->orWhere('ip_address', 'like', '%' . $request->input('search') . '%');
            });
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');

        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->input('per_page', 10);
        
        $servers = $query
            ->select('id', 'name', 'ip_address', 'provider', 'status', 'cpu_cores', 'ram_mb', 'storage_gb', 'created_at')
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage);

        return response()->json($servers);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreServerRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreServerRequest $request)
    {
        $server = Server::create($request->validated());
        return response()->json($server, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Server  $server
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Server $server)
    {
        return response()->json($server);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\StoreServerRequest  $request
     * @param  \App\Models\Server  $server
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(StoreServerRequest $request, Server $server)
    {
        $server->update($request->validated());
        return response()->json($server);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Server  $server
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Server $server)
    {
        try {
            $server->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete the server.'], 500);
        }
    }

    /**
     * Perform a bulk action on multiple servers.
     */
    public function bulkActions(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete,update_status',
            'ids' => 'required|array',
            'ids.*' => 'exists:servers,id',
            'status' => 'required_if:action,update_status|in:active,inactive,maintenance',
        ]);

        $ids = $request->input('ids');
        $action = $request->input('action');
        $status = $request->input('status');

        try {
            DB::beginTransaction();
            switch ($action) {
                case 'delete':
                    Server::whereIn('id', $ids)->delete();
                    break;
                case 'update_status':
                    Server::whereIn('id', $ids)->update(['status' => $status]);
                    break;
            }
            DB::commit();
            return response()->json(['message' => 'Bulk action successful.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Bulk action failed.'], 500);
        }
    }
}
