<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\AccountResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AccountController extends ApiController
{
    /**
     * List the company's chart of accounts with current balances.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $accounts = $this->tenant($request)->accounts()->orderBy('code')->get();

        return AccountResource::collection($accounts);
    }
}
