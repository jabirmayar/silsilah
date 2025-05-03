<?php

namespace App\Http\Controllers;

use Storage;
use App\User;
use App\Couple;
use App\UserMetadata;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Collection;
use App\Jobs\Images\OptimizeImages;
use Illuminate\Support\Facades\Auth;
use App\Jobs\Users\DeleteAndReplaceUser;
use App\Http\Requests\Users\UpdateRequest;
use Illuminate\Pagination\LengthAwarePaginator;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::check() || !is_system_admin(Auth::user())) {
            abort(403, 'Unauthorized action.');
        }

        $q = $request->get('q');
        $query = User::with('father', 'mother', 'family.parent', 'subFamily')
                     ->orderBy('created_at', 'desc');

        if ($q) {
            $query->where(function ($subQuery) use ($q) {
                $subQuery->where('name', 'like', '%'.$q.'%')
                         ->orWhere('nickname', 'like', '%'.$q.'%');
                 $subQuery->orWhereHas('family', function ($familyQuery) use ($q) {
                    $familyQuery->where('name', 'like', '%'.$q.'%');
                 });
                 $subQuery->orWhereHas('subFamily', function ($subFamilyQuery) use ($q) {
                    $subFamilyQuery->where('name', 'like', '%'.$q.'%');
                 });
            });
        }

        $users = $query->paginate(10)->withQueryString();

        return view('users.list', compact('users', 'q'));
    }

    public function updateStatus(Request $request, User $user)
    {
        if (!Auth::check() || !is_system_admin(Auth::user())) {
            abort(403, 'Unauthorized action.');
        }

        if (Auth::user()->id === $user->id) {
            $errorMessage = 'You cannot change your own status.';
            if ($request->input('origin') === 'show') {
                return redirect()->route('users.show', $user)->with('error', $errorMessage);
            } else {
                $queryParameters = $request->except(['origin', '_token', '_method', 'status']);
                return redirect()->route('users.index', $queryParameters)->with('error', $errorMessage);
            }
        }

        $request->validate([
            'status' => ['required', Rule::in([0, 1])],
            'origin' => ['nullable', Rule::in(['index', 'show'])]
        ]);

        $user->status = $request->input('status');
        $user->save();

        $successMessage = 'User status updated successfully.';

        if ($request->input('origin') === 'show') {
            return redirect()->route('users.show', $user)
                             ->with('success', $successMessage);
        } else {
            $queryParameters = $request->except(['origin', '_token', '_method', 'status']);
            return redirect()->route('users.index', $queryParameters)
                             ->with('success', $successMessage);
        }
    }

    /**
     * Search user by keyword.
     *
     * @return \Illuminate\View\View
     */
    public function search(Request $request)
    {
        $q = $request->get('q');
        $users = [];

        if ($q) {
            $query = User::with('father', 'mother', 'family', 'subFamily');

            $query->where(function ($query) use ($q) {
                $query->where('name', 'like', '%'.$q.'%')
                      ->orWhere('nickname', 'like', '%'.$q.'%');

                $query->orWhereHas('family', function ($subQuery) use ($q) {
                    $subQuery->where('name', 'like', '%'.$q.'%');
                });

                $query->orWhereHas('subFamily', function ($subQuery) use ($q) {
                    $subQuery->where('name', 'like', '%'.$q.'%');
                });
            });

            $users = $query->orderBy('name', 'asc')->paginate(10);
        } else {
             $users = new LengthAwarePaginator([], 0, 10);
        }

        return view('users.search', compact('users'));
    }

    /**
     * Display the specified User.
     *
     * @param  \App\User  $user
     * @return \Illuminate\View\View
     */
    public function show(User $user)
    {
        $usersMariageList = $this->getUserMariageList($user);
        $allMariageList = $this->getAllMariageList();
        $malePersonList = $this->getPersonList(1);
        $femalePersonList = $this->getPersonList(2);

        return view('users.show', [
            'user' => $user,
            'usersMariageList' => $usersMariageList,
            'malePersonList' => $malePersonList,
            'femalePersonList' => $femalePersonList,
            'allMariageList' => $allMariageList,
        ]);
    }

    /**
     * Display the user's family chart.
     *
     * @param  \App\User  $user
     * @return \Illuminate\View\View
     */
    public function chart(User $user)
    {
        $father = $user->father_id ? $user->father : null;
        $mother = $user->mother_id ? $user->mother : null;

        $fatherGrandpa = $father && $father->father_id ? $father->father : null;
        $fatherGrandma = $father && $father->mother_id ? $father->mother : null;

        $motherGrandpa = $mother && $mother->father_id ? $mother->father : null;
        $motherGrandma = $mother && $mother->mother_id ? $mother->mother : null;

        $childs = $user->childs;
        $colspan = $childs->count();
        $colspan = $colspan < 4 ? 4 : $colspan;

        $siblings = $user->siblings();

        return view('users.chart', compact(
            'user', 'childs', 'father', 'mother', 'fatherGrandpa',
            'fatherGrandma', 'motherGrandpa', 'motherGrandma',
            'siblings', 'colspan'
        ));
    }

    /**
     * Show user family tree.
     *
     * @param  \App\User  $user
     * @return \Illuminate\View\View
     */
    public function tree(User $user)
    {
        $user->load('childs.childs.childs.childs.childs.childs');
    
        $buildTree = function ($person, $level = 0) use (&$buildTree) {
            $roles = [
                0 => 'Self',
                1 => 'Child',
                2 => 'Grandchild',
                3 => 'Great Grandchild',
                4 => 'Great Great Grandchild',
                5 => 'Great Great Great Grandchild',
                6 => 'Descendant',
            ];
    
            $role = $roles[$level] ?? 'Descendant';
    
            $node = [
                'id' => $person->id,
                'name' => $person->name,
                'title' => $role,
                'photo' => $person->photo_path ? asset('storage/' . $person->photo_path) : asset('images/icon_user_1.png'),
            ];
    
            if ($person->childs->isNotEmpty()) {
                $node['children'] = $person->childs->map(function ($child) use ($buildTree, $level) {
                    return $buildTree($child, $level + 1);
                })->toArray();
            }
    
            return $node;
        };
    
        $treeData = $buildTree($user);
    
        return view('users.tree', [
            'user' => $user,
            'treeData' => $treeData,
        ]);
    }    

    /**
     * Show user death info.
     *
     * @param  \App\User  $user
     * @return \Illuminate\View\View
     */
    public function death(User $user)
    {
        $mapZoomLevel = config('leaflet.detail_zoom_level');
        $mapCenterLatitude = $user->getMetadata('cemetery_location_latitude');
        $mapCenterLongitude = $user->getMetadata('cemetery_location_longitude');

        return view('users.death', compact('user', 'mapZoomLevel', 'mapCenterLatitude', 'mapCenterLongitude'));
    }

    /**
     * Show the form for editing the specified User.
     *
     * @param  \App\User  $user
     * @return \Illuminate\View\View
     */
    public function edit(User $user)
    {
        $this->authorize('edit', $user);

        $replacementUsers = [];
        if (request('action') == 'delete') {
            $replacementUsers = $this->getPersonList($user->gender_id);
        }

        $validTabs = ['death', 'contact_address', 'login_account'];

        $mapZoomLevel = config('leaflet.zoom_level');
        $mapCenterLatitude = $user->getMetadata('cemetery_location_latitude');
        $mapCenterLongitude = $user->getMetadata('cemetery_location_longitude');
        if ($mapCenterLatitude && $mapCenterLongitude) {
            $mapZoomLevel = config('leaflet.detail_zoom_level');
        }
        $mapCenterLatitude = $mapCenterLatitude ?: config('leaflet.map_center_latitude');
        $mapCenterLongitude = $mapCenterLongitude ?: config('leaflet.map_center_longitude');

        return view('users.edit', compact(
            'user', 'replacementUsers', 'validTabs', 'mapZoomLevel', 'mapCenterLatitude', 'mapCenterLongitude'
        ));
    }

    /**
     * Update the specified User in storage.
     *
     * @param  \App\Http\Requests\Users\UpdateRequest  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateRequest $request, User $user)
    {
        $userAttributes = $request->validated();
        $user->update($userAttributes);
        $userAttributes = collect($userAttributes);

        $this->updateUserMetadata($user, $userAttributes);

        return redirect()->route('users.show', $user->id);
    }

    /**
     * Remove the specified User from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, User $user)
    {
        $this->authorize('delete', $user);

        if ($request->has('replace_delete_button')) {
            $attributes = $request->validate([
                'replacement_user_id' => 'required|exists:users,id',
            ], [
                'replacement_user_id.required' => __('validation.user.replacement_user_id.required'),
            ]);

            $this->dispatchNow(new DeleteAndReplaceUser($user, $attributes['replacement_user_id']));

            return redirect()->route('users.show', $attributes['replacement_user_id']);
        }

        $request->validate([
            'user_id' => 'required',
        ]);

        if ($request->get('user_id') == $user->id && $user->delete()) {
            return redirect()->route('users.search');
        }

        return back();
    }

    /**
     * Upload users photo.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function photoUpload(Request $request, User $user)
    {
        $request->validate([
            'photo' => 'required|image|max:10000',
        ]);

        if (Storage::exists($user->photo_path)) {
            Storage::delete($user->photo_path);
        }

        $user->photo_path = $request->photo->store('images');
        $user->save();

        OptimizeImages::dispatch([$user->photo_path]);

        return back();
    }

    /**
     * Get User list based on gender.
     *
     * @param int $genderId
     *
     * @return \Illuminate\Support\Collection
     */
    private function getPersonList(int $genderId)
    {
        return User::where('gender_id', $genderId)->pluck('nickname', 'id');
    }

    /**
     * Get marriage list of a user.
     *
     * @param \App\User $user
     *
     * @return array
     */
    private function getUserMariageList(User $user)
    {
        $usersMariageList = [];

        foreach ($user->couples as $spouse) {
            $usersMariageList[$spouse->pivot->id] = $user->name.' & '.$spouse->name;
        }

        return $usersMariageList;
    }

    /**
     * Get all marriage list.
     *
     * @return array
     */
    private function getAllMariageList()
    {
        $allMariageList = [];

        foreach (Couple::with('husband', 'wife')->get() as $couple) {
            $allMariageList[$couple->id] = $couple->husband->name.' & '.$couple->wife->name;
        }

        return $allMariageList;
    }

    private function updateUserMetadata(User $user, Collection $userAttributes)
    {
        foreach (User::METADATA_KEYS as $key) {
            if ($userAttributes->has($key) == false) {
                continue;
            }
            $userMeta = UserMetadata::firstOrNew(['user_id' => $user->id, 'key' => $key]);
            if (!$userMeta->exists) {
                $userMeta->id = Uuid::uuid4()->toString();
            }
            $userMeta->value = $userAttributes->get($key);
            $userMeta->save();
        }
    }
}
