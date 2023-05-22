<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Services\ArticlesService;
use App\Http\Controllers\Controller;
use App\Http\Requests\ArticleStoreRequest;
use App\Http\Requests\ArticleUpdateRequest;

class ArticleController extends Controller
{
    /**
     * @param \App\Services\ArticlesService $service
     */
    public function __construct(
        protected ArticlesService $service
    ) {
        //
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $articles = $this->service->getBySearchTerm($request->query('search'));

        return new JsonResponse($articles);
    }

    /**
     * @param \App\Http\Requests\ArticleStoreRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ArticleStoreRequest $request): JsonResponse
    {
        $id = $this->service->create($request->toArticlesAttributes());

        return new JsonResponse(compact('id'), 201);
    }

    /**
     * @param \App\Http\Requests\ArticleUpdateRequest $request
     * @param string $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(ArticleUpdateRequest $request, string $id): JsonResponse
    {
        $changes = $this->service->update($id, $request->toArticlesAttributes());

        return new JsonResponse($changes);
    }

    /**
     * @param string $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(string $id): Response
    {
        $this->service->destroy($id);

        return new Response(status: 204); // no content
    }
}
