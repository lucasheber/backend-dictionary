<?php

namespace App\Http\Controllers;

use App\Models\Dictionary;
use Illuminate\Http\Request;

class DictionaryController extends Controller
{
    /**
     * Display a listing of the words in the dictionary.
     * 
     */
    public function index(Request $request, string $lang)
    {

        // Validate the language parameter
        if ($lang !== 'en') {
            return response()->json(['error' => 'Invalid language'], 400);
        }

        // Get the search query, limit, and page from the request
        $search = $request->query('search', null);
        $limit = (int) $request->query('limit', 50);
        $page = (int) $request->query('page', 1);

        // Validate the limit and page parameters
        if ($limit < 1 || $page < 1) {
            return response()->json(['error' => 'Invalid limit or page number'], 400);
        }

        $totalDocs = Dictionary::where('lang', $lang)
            ->when($search, function ($query) use ($search) {
                return $query->where('word', 'LIKE', "%{$search}%");
            })
            ->count('id');

        $results = Dictionary::select('word')
            ->where('lang', $lang)
            ->when($search, function ($query) use ($search) {
                return $query->where('word', 'LIKE', "%{$search}%");
            })
            ->orderBy('word')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $totalPages = ceil($totalDocs / $limit);
        $hasNext = $page < $totalPages;
        $hasPrev = $page > 1;

        $response = [
            'results' => array_map(fn($item) => $item['word'], $results->toArray()),
            'totalDocs' => $totalDocs,
            'page' => $page,
            'totalPages' => $totalPages,
            'hasNext' => $hasNext,
            'hasPrev' => $hasPrev,
        ];
        return response()->json($response);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $lang, string $word)
    {
        $dictionary = Dictionary::where('word', mb_convert_case($word, MB_CASE_LOWER))
            ->where('lang', $lang)
            ->first();

        // Check if the word exists in the dictionary
        if (!$dictionary) {
            return response()->json(['error' => 'Word not found'], 404);
        }

        // Return the word and its definition
        return response()->json([
            'word' => $dictionary->word,
            'definition' => $dictionary->definition,
            'lang' => $dictionary->lang,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Dictionary $dictionary)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Dictionary $dictionary)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Dictionary $dictionary)
    {
        //
    }
}
