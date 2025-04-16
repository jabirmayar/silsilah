<?php

namespace App\Http\Controllers;

use App\User;
use App\Couple;
use Ramsey\Uuid\Uuid;
use App\Family;
use Illuminate\Http\Request;

class FamilyActionsController extends Controller
{

    public function setFamily(Request $request, $userId)
    {
        $request->validate([
            'family_id' => 'nullable|exists:families,id',
            'new_family_name' => 'nullable|string|max:100',
            'new_family_description' => 'nullable|string',
            'parent_family_id' => 'nullable|exists:families,id',
        ]);
        
        $user = User::findOrFail($userId);
        
        if ($request->filled('new_family_name')) {
            $family = Family::create([
                'id' => (string) Uuid::uuid4(),
                'name' => $request->new_family_name,
                'description' => $request->new_family_description,
                'parent_id' => $request->parent_family_id ?: null,
            ]);
            
            $user->family_id = $family->id;
        } elseif ($request->filled('family_id')) {
            $user->family_id = $request->family_id;
        }
        
        $user->save();
        
        return redirect()->route('users.show', $userId);
    }

    public function searchFamily(Request $request)
    {
        $term = $request->input('q');
        $page = $request->input('page', 1);
        $perPage = 10;
        
        $families = Family::where('name', 'like', "%{$term}%")
            ->orderBy('name')
            ->paginate($perPage, ['*'], 'page', $page);
        
        $formattedFamilies = $families->map(function($family) {
            return [
                'id' => $family->id,
                'text' => $family->name,
            ];
        });
        
        return response()->json([
            'items' => $formattedFamilies,
            'total_count' => $families->total()
        ]);
    }

    public function setParentFamily(Request $request, $userId)
    {
        $request->validate([
            'parent_family_id' => 'nullable|exists:families,id',
            'new_parent_family_name' => 'nullable|string|max:100',
            'new_parent_family_description' => 'nullable|string',
        ]);
        
        $user = User::findOrFail($userId);
        
        if (!$user->family_id) {
            return back()->with('error', __('User must have a family before setting a parent family'));
        }
        
        $userFamily = Family::findOrFail($user->family_id);
        
        if ($request->filled('new_parent_family_name')) {
            $parentFamily = Family::create([
                'id' => (string) Uuid::uuid4(),
                'name' => $request->new_parent_family_name,
                'description' => $request->new_parent_family_description,
            ]);
            
            $userFamily->parent_id = $parentFamily->id;
        } elseif ($request->filled('parent_family_id')) {

            if ($request->parent_family_id == $userFamily->id) {
                return back()->with('error', __('A family cannot be its own parent'));
            }
            
            $parentId = $request->parent_family_id;
            $checkedIds = [$userFamily->id];
            
            while ($parentId) {
                if (in_array($parentId, $checkedIds)) {
                    return back()->with('error', __('Circular family hierarchy detected'));
                }
                
                $checkedIds[] = $parentId;
                $parentFamily = Family::find($parentId);
                $parentId = $parentFamily ? $parentFamily->parent_id : null;
            }
            
            $userFamily->parent_id = $request->parent_family_id;
        } else {
            $userFamily->parent_id = null;
        }
        
        $userFamily->save();
        
        return redirect()->route('users.show', $userId)->with('success', __('Family parent updated'));
    }

    public function removeParentFamily($userId, $familyId)
    {
        $user = User::findOrFail($userId);
        $family = Family::findOrFail($familyId);
        
        if ($user->id != $family->manager_id || !is_system_admin($user)) {
            return back()->with('error', __('Not authorized'));
        }
        
        $family->parent_id = null;
        $family->save();
        
        return redirect()->route('users.show', $userId)->with('success', __('Parent family removed'));
    }

    public function addChildFamily(Request $request, $userId)
    {
        $request->validate([
            'child_family_id' => 'required|exists:families,id',
        ]);
        
        $user = User::findOrFail($userId);
        
        if (!$user->family_id) {
            return back()->with('error', __('User must have a family before adding child families'));
        }
        
        $userFamily = Family::findOrFail($user->family_id);
        $childFamily = Family::findOrFail($request->child_family_id);
        
        if ($childFamily->id == $userFamily->id) {
            return back()->with('error', __('A family cannot be its own child'));
        }
        
        if ($this->wouldCreateCircularReference($userFamily->id, $childFamily->id)) {
            return back()->with('error', __('This would create a circular reference in the family hierarchy'));
        }
        
        $childFamily->parent_id = $userFamily->id;
        $childFamily->save();
        
        return redirect()->route('users.show', $userId)->with('success', __('Child family added'));
    }

    public function removeChildFamily($userId, $familyId)
    {
        $user = User::findOrFail($userId);
        
        if (!$user->family_id) {
            return redirect()->route('users.show', $userId)->with('error', __('User does not have a family'));
        }
        
        $childFamily = Family::findOrFail($familyId);
        
        if ($childFamily->parent_id != $user->family_id) {
            return redirect()->route('users.show', $userId)->with('error', __('This is not a child of your family'));
        }

        if ($user->id != $childFamily->manager_id || !is_system_admin($user)) {
            return redirect()->route('users.show', $userId)->with('error', __('Not authorized'));
        }
        
        $childFamily->parent_id = null;
        $childFamily->save();
        
        return redirect()->route('users.show', $userId)->with('success', __('Child family removed'));
    }

    private function wouldCreateCircularReference($potentialParentId, $familyId)
    {
        $current = Family::find($potentialParentId);
        $checkedIds = [$familyId];
        
        while ($current) {
            if (in_array($current->id, $checkedIds)) {
                return true; 
            }
            
            $checkedIds[] = $current->id;
            $current = $current->parent;
        }
        
        return false;
    }

    /**
     * Set father for a user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function setFather(Request $request, User $user)
    {
        $request->validate([
            'set_father_id' => 'nullable',
            'set_father'    => 'required_without:set_father_id|max:255',
        ]);

        if ($request->get('set_father_id')) {
            $user->father_id = $request->get('set_father_id');
            $user->save();
        } else {
            $father = new User;
            $father->id = Uuid::uuid4()->toString();
            $father->name = $request->get('set_father');
            $father->nickname = $request->get('set_father');
            $father->gender_id = 1;
            $father->manager_id = auth()->id();

            $user->setFather($father);
        }

        return back();
    }

    /**
     * Set mother for a user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function setMother(Request $request, User $user)
    {
        $request->validate([
            'set_mother_id' => 'nullable',
            'set_mother'    => 'required_without:set_mother_id|max:255',
        ]);

        if ($request->get('set_mother_id')) {
            $user->mother_id = $request->get('set_mother_id');
            $user->save();
        } else {
            $mother = new User;
            $mother->id = Uuid::uuid4()->toString();
            $mother->name = $request->get('set_mother');
            $mother->nickname = $request->get('set_mother');
            $mother->gender_id = 2;
            $mother->manager_id = auth()->id();

            $user->setMother($mother);
        }

        return back();
    }

    /**
     * Add child for a user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addChild(Request $request, User $user)
    {
        $request->validate([
            'add_child_name'        => 'required|string|max:255',
            'add_child_gender_id'   => 'required|in:1,2',
            'add_child_parent_id'   => 'nullable|exists:couples,id',
            'add_child_birth_order' => 'nullable|numeric',
        ]);

        $child = new User;
        $child->id = Uuid::uuid4()->toString();
        $child->name = $request->get('add_child_name');
        $child->nickname = $request->get('add_child_name');
        $child->gender_id = $request->get('add_child_gender_id');
        $child->parent_id = $request->get('add_child_parent_id');
        $child->birth_order = $request->get('add_child_birth_order');
        $child->manager_id = auth()->id();

        \DB::beginTransaction();
        $child->save();

        if ($request->get('add_child_parent_id')) {
            $couple = Couple::find($request->get('add_child_parent_id'));
            $child->father_id = $couple->husband_id;
            $child->mother_id = $couple->wife_id;
            $child->save();
        } else {
            if ($user->gender_id == 1) {
                $child->setFather($user);
            } else {
                $child->setMother($user);
            }

        }

        \DB::commit();

        return back();
    }

    /**
     * Add wife for male user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addWife(Request $request, User $user)
    {
        $request->validate([
            'set_wife_id'   => 'nullable',
            'set_wife'      => 'required_without:set_wife_id|max:255',
            'marriage_date' => 'nullable|date|date_format:Y-m-d',
        ]);

        if ($request->get('set_wife_id')) {
            $wife = User::findOrFail($request->get('set_wife_id'));
        } else {
            $wife = new User;
            $wife->id = Uuid::uuid4()->toString();
            $wife->name = $request->get('set_wife');
            $wife->nickname = $request->get('set_wife');
            $wife->gender_id = 2;
            $wife->manager_id = auth()->id();
        }

        $user->addWife($wife, $request->get('marriage_date'));

        return back();
    }

    /**
     * Add husband for female user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addHusband(Request $request, User $user)
    {
        $this->validate($request, [
            'set_husband_id' => 'nullable',
            'set_husband'    => 'required_without:set_husband_id|max:255',
            'marriage_date'  => 'nullable|date|date_format:Y-m-d',
        ]);

        if ($request->get('set_husband_id')) {
            $husband = User::findOrFail($request->get('set_husband_id'));
        } else {
            $husband = new User;
            $husband->id = Uuid::uuid4()->toString();
            $husband->name = $request->get('set_husband');
            $husband->nickname = $request->get('set_husband');
            $husband->gender_id = 1;
            $husband->manager_id = auth()->id();
        }

        $user->addHusband($husband, $request->get('marriage_date'));

        return back();
    }

    /**
     * Set parent for a user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function setParent(Request $request, User $user)
    {
        $user->parent_id = $request->get('set_parent_id');
        $user->save();

        return redirect()->route('users.show', $user);
    }
}
