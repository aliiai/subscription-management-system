<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\JournalEntryResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class JournalEntryController extends ApiController
{
    /**
     * List the company's journal entries with optional date/source filters.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $entries = $this->tenant($request)->journalEntries()
            ->with('lines.account')
            ->when($request->date('from'), fn ($query) => $query->whereDate('entry_date', '>=', $request->date('from')))
            ->when($request->date('to'), fn ($query) => $query->whereDate('entry_date', '<=', $request->date('to')))
            ->when($request->filled('source_type'), fn ($query) => $query->where('source_type', $request->string('source_type')->value()))
            ->latest('entry_date')
            ->latest('id')
            ->paginate($request->integer('per_page', 15));

        return JournalEntryResource::collection($entries);
    }

    /**
     * Show a single journal entry with its debit/credit lines.
     */
    public function show(Request $request, int $entry): JournalEntryResource
    {
        return JournalEntryResource::make(
            $this->tenant($request)->journalEntries()->with('lines.account')->findOrFail($entry)
        );
    }
}
