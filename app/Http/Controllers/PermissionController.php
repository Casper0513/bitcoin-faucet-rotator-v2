<?php

namespace App\Http\Controllers;

use App\Helpers\Functions;
use App\Http\Requests\CreatePermissionRequest;
use App\Http\Requests\UpdatePermissionRequest;
use App\Repositories\PermissionRepository;
use App\Http\Controllers\AppBaseController;
use Illuminate\Http\Request;
use Flash;
use Illuminate\Support\Facades\Auth;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

class PermissionController extends AppBaseController
{
    /** @var  PermissionRepository */
    private $permissionRepository;

    public function __construct(PermissionRepository $permissionRepo)
    {
        $this->permissionRepository = $permissionRepo;
        $this->middleware('auth');
    }

    /**
     * Display a listing of the Permission.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        Functions::userCanAccessArea(Auth::user(), 'permissions.index', [], []);
        $this->permissionRepository->pushCriteria(new RequestCriteria($request));
        $permissions = $this->permissionRepository->all();

        return view('permissions.index')
            ->with('permissions', $permissions);
    }

    /**
     * Show the form for creating a new Permission.
     *
     * @return Response
     */
    /*public function create()
    {
        Functions::userCanAccessArea(Auth::user(), 'permissions.create');
        return view('permissions.create');
    }*/

    /**
     * Store a newly created Permission in storage.
     *
     * @param CreatePermissionRequest $request
     *
     * @return Response
     */
    /*public function store(CreatePermissionRequest $request)
    {
        Functions::userCanAccessArea(Auth::user(), 'permissions.store');
        $input = $request->all();

        $permission = $this->permissionRepository->create($input);

        Flash::success('Permission saved successfully.');

        return redirect(route('permissions.index'));
    }*/

    /**
     * Display the specified Permission.
     *
     * @param  string $slug
     *
     * @return Response
     */
    public function show($slug)
    {
        Functions::userCanAccessArea(Auth::user(), 'permissions.show', ['slug' => $slug], ['slug' => $slug]);
        $permission = $this->permissionRepository->findByField('slug', $slug)->first();

        if (empty($permission)) {
            Flash::error('Permission not found');

            return redirect(route('permissions.index'));
        }

        return view('permissions.show')->with('permission', $permission);
    }

    /**
     * Show the form for editing the specified Permission.
     *
     * @param  string $slug
     *
     * @return Response
     */
    public function edit($slug)
    {
        Functions::userCanAccessArea(Auth::user(), 'permissions.edit', ['slug' => $slug], ['slug' => $slug]);
        $permission = $this->permissionRepository->findByField('slug', $slug)->first();

        if (empty($permission)) {
            Flash::error('Permission not found');

            return redirect(route('permissions.index'));
        }

        return view('permissions.edit')->with('permission', $permission);
    }

    /**
     * Update the specified Permission in storage.
     *
     * @param  int              $id
     * @param UpdatePermissionRequest $request
     *
     * @return Response
     */
    public function update($slug, UpdatePermissionRequest $request)
    {
        Functions::userCanAccessArea(Auth::user(), 'permissions.update', ['slug' => $slug], ['slug' => $slug]);
        $permission = $this->permissionRepository->findByField('slug', $slug)->first();

        if (empty($permission)) {
            Flash::error('Permission not found');

            return redirect(route('permissions.index'));
        }

        $permission = $this->permissionRepository->update($request->all(), $permission->id);

        Flash::success('Permission updated successfully.');

        return redirect(route('permissions.index'));
    }

    /**
     * Remove the specified Permission from storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    /*public function destroy($id)
    {
        Functions::userCanAccessArea(Auth::user(), 'permissions.destroy');
        $permission = $this->permissionRepository->findWithoutFail($id);

        if (empty($permission)) {
            Flash::error('Permission not found');

            return redirect(route('permissions.index'));
        }

        $this->permissionRepository->delete($id);

        Flash::success('Permission deleted successfully.');

        return redirect(route('permissions.index'));
    }*/
}
