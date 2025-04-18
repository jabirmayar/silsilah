<?php

namespace App\Http\Controllers;

use App\Family;
use Illuminate\Http\Request;

class FamilyController extends Controller
{

    public function index(Request $request)
    {
        $search = $request->input('search');

        $families = Family::query()
        ->when($search, fn($query) =>
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
        )
        ->orderBy('created_at', 'desc')
        ->paginate(10)
        ->withQueryString();
    
        return view('families.index', compact('families', 'search'));
    }

    public function search(Request $request)
    {
        $search = $request->input('q');
        $page = $request->input('page', 1);
        $perPage = 10;
        
        $families = Family::where('name', 'like', "%{$search}%")
            ->orderBy('name')
            ->paginate($perPage, ['*'], 'page', $page);
        
        return response()->json([
            'items' => collect($families->items())->map(function ($family) {
                return ['id' => $family->id, 'text' => $family->name];
            }),
            'total_count' => $families->total()
        ]);
    }

    public function create()
    {
        $selectedFamily = null;
        if (old('parent_id')) {
            $selectedFamily = Family::find(old('parent_id'));
        }
        
        return view('families.create', compact('selectedFamily'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:families,id',
        ]);

        $data = $request->all();
        $data['manager_id'] = auth()->id();

        Family::create($data);

        return redirect()->route('families.index')->with('success', __('app.family_created_successfully'));
    }



    public function show(Family $family)
    {
        $family->load('children', 'parent');
    
        $ancestors = $family->ancestors();
        $descendants = $family->descendants();
        $children = $family->children;
    
        return view('families.show', compact('family', 'ancestors', 'descendants', 'children'));
    }

    public function edit(Family $family)
    {
        $this->authorize('edit', $family);
    
        $families = Family::where('id', '!=', $family->id)->pluck('name', 'id');
        return view('families.edit', compact('family', 'families'));
    }

    public function update(Request $request, Family $family)
    {
        $this->authorize('edit', $family);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:families,id|not_in:' . $family->id,
        ]);

        if ($this->wouldCreateCircularReference($request->parent_id, $family->id)) {
            return redirect()->back()->with('error', __('app.circular_reference_error'));
        }

        $family->update([
            'name' => $request->name,
            'description' => $request->description,
            'parent_id' => $request->parent_id,
        ]);

        return redirect()->route('families.show', $family)->with('success', __('app.family_updated'));
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

    
}
