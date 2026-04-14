<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeBase;
use App\Services\KnowledgeBaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class KnowledgeBaseController extends Controller
{
    protected KnowledgeBaseService $kbService;

    public function __construct(KnowledgeBaseService $kbService)
    {
        $this->kbService = $kbService;
    }

    public function index(Request $request): View
    {
        $query = KnowledgeBase::query();

        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        if ($request->has('search') && $request->search) {
            $query->search($request->search);
        }

        $knowledgeBases = $query->orderBy('updated_at', 'desc')->paginate(15);
        $categories = KnowledgeBase::categories();

        return view('knowledge-base.index', compact('knowledgeBases', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category' => 'required|in:company,account,resource,faq,script,policy',
            'content' => 'nullable|string',
            'tags' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['tags'] = $validated['tags'] 
            ? array_map('trim', explode(',', $validated['tags']))
            : [];

        $kb = $this->kbService->createOrUpdate($validated);

        return redirect()->route('knowledge-base.index')
            ->with('success', 'Knowledge base entry created successfully.');
    }

    public function show(int $id): View
    {
        $kb = KnowledgeBase::with('entries')->findOrFail($id);
        return view('knowledge-base.show', compact('kb'));
    }

    public function edit(int $id): View
    {
        $kb = KnowledgeBase::findOrFail($id);
        $categories = KnowledgeBase::categories();
        return view('knowledge-base.edit', compact('kb', 'categories'));
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category' => 'required|in:company,account,resource,faq,script,policy',
            'content' => 'nullable|string',
            'tags' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['id'] = $id;
        $validated['tags'] = $validated['tags'] 
            ? array_map('trim', explode(',', $validated['tags']))
            : [];

        $kb = $this->kbService->createOrUpdate($validated);

        return redirect()->route('knowledge-base.index')
            ->with('success', 'Knowledge base entry updated successfully.');
    }

    public function destroy(int $id)
    {
        $kb = KnowledgeBase::findOrFail($id);
        $kb->delete();

        return redirect()->route('knowledge-base.index')
            ->with('success', 'Knowledge base entry deleted successfully.');
    }

    public function apiSearch(Request $request)
    {
        $query = $request->input('q', '');
        $category = $request->input('category');
        $limit = $request->input('limit', 10);

        if (empty($query)) {
            return response()->json([]);
        }

        $results = $this->kbService->search($query, $category, $limit);

        return response()->json($results);
    }
}
