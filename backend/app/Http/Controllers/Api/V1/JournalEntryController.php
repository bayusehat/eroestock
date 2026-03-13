<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreJournalEntryRequest;
use App\Http\Requests\Api\V1\UpdateJournalEntryRequest;
use App\Http\Resources\V1\JournalEntryResource;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Traits\GeneratesNumber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JournalEntryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = JournalEntry::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('journal_no', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $journalEntries = $query->latest('date')->paginate($request->get('per_page', 25));

        return response()->json([
            'success' => true,
            'message' => 'Journal entries retrieved successfully',
            'data' => JournalEntryResource::collection($journalEntries),
            'meta' => [
                'current_page' => $journalEntries->currentPage(),
                'per_page' => $journalEntries->perPage(),
                'total' => $journalEntries->total(),
                'last_page' => $journalEntries->lastPage(),
            ],
        ]);
    }

    public function store(StoreJournalEntryRequest $request): JsonResponse
    {
        $journalEntry = DB::transaction(function () use ($request) {
            $journalNo = GeneratesNumber::generateNumber('JE', 'journal_entries', 'journal_no', 'Y');

            $journalEntry = JournalEntry::create([
                'journal_no' => $journalNo,
                'date' => $request->date,
                'description' => $request->description,
                'created_by' => $request->user()->id,
            ]);

            foreach ($request->lines as $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $line['account_id'],
                    'debit' => $line['debit'] ?? 0,
                    'credit' => $line['credit'] ?? 0,
                    'description' => $line['description'] ?? null,
                ]);
            }

            return $journalEntry->load('lines.account');
        });

        return response()->json([
            'success' => true,
            'message' => 'Journal entry created successfully',
            'data' => new JournalEntryResource($journalEntry),
        ], 201);
    }

    public function show(JournalEntry $journalEntry): JsonResponse
    {
        $journalEntry->load('lines.account');

        return response()->json([
            'success' => true,
            'message' => 'Journal entry retrieved successfully',
            'data' => new JournalEntryResource($journalEntry),
        ]);
    }

    public function update(UpdateJournalEntryRequest $request, JournalEntry $journalEntry): JsonResponse
    {
        $journalEntry = DB::transaction(function () use ($request, $journalEntry) {
            $journalEntry->update(array_filter($request->only(['date', 'description']), fn ($v) => $v !== null));

            if ($request->has('lines')) {
                $journalEntry->lines()->delete();

                foreach ($request->lines as $line) {
                    JournalEntryLine::create([
                        'journal_entry_id' => $journalEntry->id,
                        'account_id' => $line['account_id'],
                        'debit' => $line['debit'] ?? 0,
                        'credit' => $line['credit'] ?? 0,
                        'description' => $line['description'] ?? null,
                    ]);
                }
            }

            return $journalEntry->fresh('lines.account');
        });

        return response()->json([
            'success' => true,
            'message' => 'Journal entry updated successfully',
            'data' => new JournalEntryResource($journalEntry),
        ]);
    }

    public function destroy(JournalEntry $journalEntry): JsonResponse
    {
        $journalEntry->delete();

        return response()->json([
            'success' => true,
            'message' => 'Journal entry deleted successfully',
        ]);
    }
}
