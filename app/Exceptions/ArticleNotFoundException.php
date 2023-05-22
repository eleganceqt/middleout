<?php

namespace App\Exceptions;

use RuntimeException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ArticleNotFoundException extends RuntimeException
{
    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function render(Request $request)
    {
        if ($request->expectsJson()) {
            throw new NotFoundHttpException($this->getMessage(), $this);
        }
    }
}
