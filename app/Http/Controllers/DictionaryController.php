<?php

namespace App\Http\Controllers;

use App\Models\Dictionary;
use App\Services\WordsApiService;
use Illuminate\Http\Request;

/**
 * @OA\Info(
 *     title="Dictionary API",
 *     version="1.0.0",
 *     description="API for managing dictionary words and user favorites.",
 *     @OA\Contact(
 *         name="API Support",
 *         email="lucas.heber07@gmailcom",
 *     )
 * )
 */
class DictionaryController extends Controller
{
    protected $wordsApi;

    public function __construct(WordsApiService $wordsApi)
    {
        $this->wordsApi = $wordsApi;
    }

   /**
    * @OA\Get(
    *     path="/entries/{lang}",
    *     summary="Get dictionary words",
    *     description="Get a list of dictionary words with optional search, limit, and page parameters. The unique language available is 'en'.",
    *     operationId="getDictionaryWords",
    *     tags={"Dictionary"},
    *     security={{"sanctum":{}}},
    *     @OA\Parameter(
    *         name="lang",
    *         in="path",
    *         required=true,
    *         description="Language code (e.g., 'en' for English)",
    *         @OA\Schema(
    *             type="string",
    *             example="en"
    *         )
    *     ),
    *     @OA\Parameter(
    *         name="search",
    *         in="query",
    *         required=false,
    *         description="Search term to filter words",
    *         @OA\Schema(
    *             type="string",
    *             example="example"
    *         )
    *     ),
    *     @OA\Parameter(
    *         name="limit",
    *         in="query",
    *         required=false,
    *         description="Number of results per page",
    *         @OA\Schema(
    *             type="integer",
    *             example=50
    *         )
    *     ),
    *     @OA\Parameter(
    *         name="page",
    *         in="query",
    *         required=false,
    *         description="Page number for pagination",
    *         @OA\Schema(
    *             type="integer",
    *             example=1
    *         )
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Successful response",
    *         @OA\JsonContent(
    *             @OA\Property(
    *                 property="results",
    *                 type="array",
    *                 @OA\Items(
    *                     type="string",
    *                     example="example"
    *                 )
    *             ),
    *             @OA\Property(
    *                 property="totalDocs",
    *                 type="integer",
    *                 example=100
    *             ),
    *             @OA\Property(
    *                 property="page",
    *                 type="integer",
    *                 example=1
    *             ),
    *             @OA\Property(
    *                 property="totalPages",
    *                 type="integer",
    *                 example=10
    *             ),
    *             @OA\Property(
    *                 property="hasNext",
    *                 type="boolean",
    *                 example=true
    *             ),
    *             @OA\Property(
    *                 property="hasPrev",
    *                 type="boolean",
    *                 example=false
    *             ),
    *             @OA\Property(
    *                 property="x-cache",
    *                 type="string",
    *                 example="MISS"
    *             ),
    *             @OA\Property(
    *                 property="x-response-time",
    *                 type="string",
    *                 example="123.45ms"
    *             )
    *         )
    *     ),
    *     @OA\Response(
    *         response=400,
    *         description="Invalid request parameters",
    *         @OA\JsonContent(
    *             @OA\Property(
    *                 property="error",
    *                 type="string",
    *                 example="Invalid language"
    *             )
    *         )
    *     ),
    *     @OA\Response(
    *         response=404,
    *         description="Resource not found",
    *         @OA\JsonContent(
    *             @OA\Property(
    *                 property="error",
    *                 type="string",
    *                 example="Word not found"
    *             )
    *         )
    *     )
    * )
    *
    */
    public function index(Request $request, string $lang)
    {
        $startTime = microtime(true);

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

        // Caching the dictionary words
        $cacheKey = "dictionary_words_{$lang}_{$search}_{$limit}_{$page}";
        $cachedResults = cache()->get($cacheKey);
        if ($cachedResults) {
            $duration = (microtime(true) - $startTime) * 1000;
            $cachedResults['x-cache'] = 'HIT';
            $cachedResults['x-response-time'] = round($duration, 2) . 'ms';
            return response()->json($cachedResults);
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
            'results' => array_map(fn ($item) => $item['word'], $results->toArray()),
            'totalDocs' => $totalDocs,
            'page' => $page,
            'totalPages' => $totalPages,
            'hasNext' => $hasNext,
            'hasPrev' => $hasPrev,
        ];

        // Cache the results for 60 minutes and add the headers
        cache()->put($cacheKey, $response, 60);
        $duration = (microtime(true) - $startTime) * 1000;
        $response['x-cache'] = 'MISS';
        $response['x-response-time'] = round($duration, 2) . 'ms';

        return response()->json($response);
    }

    /**
     * @OA\Get(
     *     path="/entries/{lang}/{word}",
     *     summary="Get word data",
     *     description="Retrieve detailed information about a specific word.",
     *     operationId="getWordData",
     *     tags={"Dictionary"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="lang",
     *         in="path",
     *         required=true,
     *         description="Language code (e.g., 'en' for English)",
     *         @OA\Schema(
     *             type="string",
     *             example="en"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="word",
     *         in="path",
     *         required=true,
     *         description="Word to retrieve data for",
     *         @OA\Schema(
     *             type="string",
     *             example="example"
     *         )
     *     ),
     *    @OA\Response(
     *        response=200,
     *       description="Successful response",
     *       @OA\JsonContent(
     *           @OA\Property(property="word", type="string", example="example"),
     *          @OA\Property(property="definition", type="string", example="A representative form or pattern"),
     *          @OA\Property(property="examples", type="array",
     *               @OA\Items(
     *                  type="string",
     *                 example="This is an example sentence."
     *                )
     *           ),
     *      ),
     *  )
     * )
    */
    public function show(string $lang, string $word)
    {
        // save the word to the user's history
        request()->user()->historyWords()->updateOrCreate(
            ['word' => mb_convert_case($word, MB_CASE_LOWER)],
            ['user_id' => request()->user()->id]
        );

        $data = $this->wordsApi->getWordData($word);
        return response()->json($data);
    }

    /**
     * @OA\Post(
     *     path="/entries/{lang}/{word}/favorite",
     *     summary="Favorite a word",
     *     description="Add a word to the user's favorites.",
     *     operationId="favoriteWord",
     *     tags={"Dictionary"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Word favorited successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Word favorited successfully")
     *         )
     *     ),
     * )
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
     * @OA\Delete(
     *     path="/entries/{lang}/{word}/favorite",
     *     summary="Unfavorite a word",
     *     description="Remove a word from the user's favorites.",
     *     operationId="unfavoriteWord",
     *     tags={"Dictionary"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="lang",
     *         in="path",
     *         required=true,
     *         description="Language code (e.g., 'en' for English)",
     *         @OA\Schema(
     *             type="string",
     *             example="en"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="word",
     *         in="path",
     *         required=true,
     *         description="Word to unfavorite",
     *         @OA\Schema(
     *             type="string",
     *             example="example"
     *         )
     *     ),
     *    @OA\Response(
     *       response=200,
     *      description="Word unfavorited successfully",
     *     @OA\JsonContent(
     *          @OA\Property(property="message", type="string", example="Word unfavorited successfully")
     *        )
     *     ),
     *    @OA\Response(
     *        response=404,
     *       description="Word not found",
     *      @OA\JsonContent(
     *           @OA\Property(property="error", type="string", example="Word not found")
     *        )
     *    ),
     * )
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
     * @OA\Get(
     *     path="/user/me/favorites",
     *     summary="Get user's favorite words",
     *     description="Retrieve the user's favorite words.",
     *     operationId="getUserFavoriteWords",
     *     tags={"User"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="word", type="string", example="example"),
     *                 @OA\Property(property="added", type="string", format="date-time", example="2023-10-01T12:00:00Z")
     *             )
     *         )
     *     ),
     * )
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

    /**
     * @OA\Get(
     *     path="/user/me/history",
     *     summary="Get user's history words",
     *     description="Retrieve the user's history words.",
     *     operationId="getUserHistoryWords",
     *     tags={"User"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="word", type="string", example="example"),
     *                 @OA\Property(property="added", type="string", format="date-time", example="2023-10-01T12:00:00Z")
     *             )
     *         )
     *     ),
     * )
     */
    public function history(Request $request)
    {
        $user = $request->user();

        // Get the user's history words
        $history = $user->historyWords()->get();

        // Map the results to include only the word and its definition
        $results = $history->map(function ($historyWord) {
            return [
                'word' => $historyWord->word,
                'added' => $historyWord->created_at,
            ];
        });

        return response()->json($results);
    }
}
