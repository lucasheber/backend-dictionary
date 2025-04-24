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
     * Favorite a word.
     */
    public function favorite(Request $request, string $lang, string $word)
    {
        $dictionary = Dictionary::where('word', mb_convert_case($word, MB_CASE_LOWER))
            ->where('lang', $lang)
            ->first();

        // Check if the word exists in the dictionary
        if (!$dictionary) {
            return response()->json(['error' => 'Word not found'], 404);
        }

        // Add the word to the user's favorites if it doesn't already exist
        $exists = $request->user()->favoriteWords()
            ->where('dictionary_id', $dictionary->id)
            ->exists();

        if ($exists) {
            return response()->json(['error' => 'Word already favorited'], 400);
        }

        $user = $request->user();
        $user->favoriteWords()->create([
            'dictionary_id' => $dictionary->id,
            'user_id' => $user->id,
        ]);

        return response()->json(['message' => 'Word favorited successfully']);
    }

    /**
     * Unfavorite a word.
     */
    public function unfavorite(Request $request, string $lang, string $word)
    {
        $dictionary = Dictionary::where('word', mb_convert_case($word, MB_CASE_LOWER))
            ->where('lang', $lang)
            ->first();

        // Check if the word exists in the dictionary
        if (!$dictionary) {
            return response()->json(['error' => 'Word not found'], 404);
        }

        // Check if the word is favorite
        $exists = $request->user()->favoriteWords()
            ->where('dictionary_id', $dictionary->id)
            ->exists();

        if (!$exists) {
            return response()->json(['error' => 'Word is not favorited'], 400);
        }

        // Remove the word from the user's favorites
        $user = $request->user();
        $user->favoriteWords()->where('dictionary_id', $dictionary->id)->delete();

        return response()->json(['message' => 'Word unfavorited successfully']);
    }

    /**
     * Display the user's favorite words.
     */
    public function favorites(Request $request)
    {
        $user = $request->user();

        // Get the user's favorite words
        $favorites = $user->favoriteWords()->with('dictionary')->get();

        // Map the results to include only the word and its definition
        $results = $favorites->map(function ($favorite) {
            return [
                'word' => $favorite->dictionary->word,
                'added' => $favorite->created_at,
            ];
        });

        return response()->json($results);
    }
}
