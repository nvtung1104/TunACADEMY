<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Bookmark;
use App\Models\Exam;
use App\Models\Lesson;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    private static array $typeMap = [
        'lesson'     => Lesson::class,
        'exam'       => Exam::class,
        'assignment' => Assignment::class,
    ];

    public function index(Request $request)
    {
        $query = auth()->user()->bookmarks()->with([
            'bookmarkable',
            'bookmarkable.subject:id,name,color,icon',
            'bookmarkable.classroom:id,name,grade_id',
            'bookmarkable.classroom.grade:id,name,level',
        ]);

        if ($request->type && isset(self::$typeMap[$request->type])) {
            $query->where('bookmarkable_type', self::$typeMap[$request->type]);
        }

        $bookmarks = $query->latest()->get()->map(function ($b) {
            $item = $b->bookmarkable;
            if (!$item) return null;

            $type = array_search($b->bookmarkable_type, self::$typeMap);
            return [
                'bookmark_id'  => $b->id,
                'type'         => $type,
                'id'           => $item->id,
                'title'        => $item->title,
                'description'  => $item->description ?? null,
                'subject'      => $item->subject ?? null,
                'classroom'    => $item->classroom ?? null,
                'saved_at'     => $b->created_at,
            ];
        })->filter()->values();

        return response()->json(['data' => $bookmarks]);
    }

    public function toggle(Request $request)
    {
        $request->validate([
            'type' => 'required|in:lesson,exam,assignment',
            'id'   => 'required|integer',
        ]);

        $modelClass = self::$typeMap[$request->type];

        $existing = auth()->user()->bookmarks()
            ->where('bookmarkable_type', $modelClass)
            ->where('bookmarkable_id', $request->id)
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['saved' => false]);
        }

        auth()->user()->bookmarks()->create([
            'bookmarkable_type' => $modelClass,
            'bookmarkable_id'   => $request->id,
        ]);

        return response()->json(['saved' => true]);
    }

    public function check(Request $request)
    {
        $request->validate([
            'type' => 'required|in:lesson,exam,assignment',
            'id'   => 'required|integer',
        ]);

        $modelClass = self::$typeMap[$request->type];

        $saved = auth()->user()->bookmarks()
            ->where('bookmarkable_type', $modelClass)
            ->where('bookmarkable_id', $request->id)
            ->exists();

        return response()->json(['saved' => $saved]);
    }
}
