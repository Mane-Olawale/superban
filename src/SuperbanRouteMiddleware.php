<?php

namespace ManeOlawale\Superban;

use Illuminate\Http\Request;

class SuperbanRouteMiddleware extends SuperbanMiddleware
{
    /**
     * @inheritDoc
     */
    protected function makeSuperban(Request $request, int $duration): Superban
    {
        /**
         * Appending a value to the key of superban has an effect on it's scope.
         * Therefore appending the path of the current request makes the ban affect 
         * the current route alone.
         */
        return new Superban($request, $duration, ':' . $request->path());
    }
}
