<?php

namespace App\Http\Controllers;

use App\Models\KanbanColumn;
use App\Models\KanbanRow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KanbanController extends Controller
{
    public function all_columns()
    {
        $user = Auth::user();
        if ($user) {
            $columns = KanbanColumn::with([])->where('userId', $user->id)->get();
            return response()->json($columns, 200);
        } else {
            return response("Unauthorised", 401);
        }
    }

    public function column(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            if ($request->has("columnId")) {
                $columnId = $request->input("columnId");
                $column = KanbanColumn::with(['kanbanRows'])->where('id', $columnId)->where('userId', $user->id)->first();
                return response()->json($column, 200);
            } else {
                return response("Column Id is missing", 400);
            }
        } else {
            return response("Unauthorised", 401);
        }
    }

    public function create_column(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            if ($request->has("name")) {
                $name = $request->input("name");
                $lastColumn = KanbanColumn::where('userId', $user->id)->orderBy('position', 'desc')->first();
                $newPosition = $lastColumn ? $lastColumn->position + 1 : 1;
                $column = KanbanColumn::create(["name" => $name, "position" => $newPosition, "userId" => $user->id]);
                return response()->json($column, 200);
            } else {
                return response("Name or position is missing", 400);
            }
        } else {
            return response("Unauthorised", 401);
        }
    }

    public function edit_column(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            if ($request->has("columnId")) {
                $columnId = $request->input("columnId");
                $column = KanbanColumn::with(['kanbanRows'])->where('id', $columnId)->where('userId', $user->id)->first();
                if (!$column) {
                    return response("Unauthorised", 401);
                }
                $column->update($request->all());
                return response()->json($column, 200);
            } else {
                return response("Name or position is missing", 400);
            }
        } else {
            return response("Unauthorised", 401);
        }
    }

    public function delete_column(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            if ($request->has("columnId")) {
                $columnId = $request->input("columnId");
                $column = KanbanColumn::with(['kanbanRows'])->where('id', $columnId)->where('userId', $user->id)->first();
                if (!$column) {
                    return response("Unauthorised", 401);
                }
                $column->delete();
                return response()->json($column, 200);
            } else {
                return response("Name or position is missing", 400);
            }
        } else {
            return response("Unauthorised", 401);
        }
    }

    public function all_rows()
    {
        $user = Auth::user();
        if ($user) {
            $rows = KanbanRow::with(['kanbanColumn' => function($query) use($user) {
                $query->where('userId', $user->id);
            }])->get();
            return response()->json($rows, 200);
        } else {
            return response("Unauthorised", 401);
        }
    }

    public function row(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            if ($request->has("columnId") && $request->has("rowId")) {
                $columnId = $request->input("columnId");
                $column = KanbanColumn::with(['kanbanRows'])->where('id', $columnId)->where('userId', $user->id)->first();
                if (!$column) {
                    return response("Unauthorised", 401);
                }
                $rowId = $request->input("rowId");
                $row = KanbanRow::with(['kanbanColumn'])->where('id', $rowId)->where('kanbanColumnId', $columnId)->first();
                return response()->json($row, 200);
            } else {
                return response("Name or position is missing", 400);
            }
        } else {
            return response("Unauthorised", 401);
        }
    }

    public function create_row(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            if ($request->has("columnId") && $request->has("title") && $request->has("subtitle")) {
                $columnId = $request->input("columnId");
                $column = KanbanColumn::with(['kanbanRows'])->where('id', $columnId)->where('userId', $user->id)->first();
                if (!$column) {
                    return response("Unauthorised", 401);
                }
                $lastRow = KanbanRow::where('kanbanColumnId', $columnId)->orderBy('position', 'desc')->first();
                $newPosition = $lastRow ? $lastRow->position + 1 : 1;
                $title = $request->input("title");
                $subtitle = $request->input("subtitle");
                $row = KanbanRow::create(['title' => $title, 'subtitle' => $subtitle, 'position' => $newPosition, 'kanbanColumnId' => $columnId]);
                return response()->json($row, 200);
            } else {
                return response("Name or position is missing", 400);
            }
        } else {
            return response("Unauthorised", 401);
        }
    }

    public function edit_row(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            if ($request->has("columnId") && $request->has("rowId")) {
                $columnId = $request->input("columnId");
                $column = KanbanColumn::with(['kanbanRows'])->where('id', $columnId)->where('userId', $user->id)->first();
                if (!$column) {
                    return response("Unauthorised", 401);
                }
                $rowId = $request->input("rowId");
                $row = KanbanRow::with(['kanbanColumn'])->where('id', $rowId)->where('kanbanColumnId', $columnId)->first();
                if (!$row) {
                    return response("Unauthorised", 401);
                }
                $row->update($request->all());
                return response()->json($row, 200);
            } else {
                return response("columnId or rowId is missing", 400);
            }
        } else {
            return response("Unauthorised", 401);
        }
    }

    public function reorder(Request $request)
    {
        $user = Auth::user();
        if ($user) {
                if ($request->has('list')) {
                    $list = $request->input('list');
                    foreach ($list as $item) {
                        KanbanRow::where('id', $item['id'])->update(['position' => $item['position'], 'kanbanColumnId' => $item['kanbanColumnId']]);
                    }
                    return response("Success", 200);
                } else {
                    return response('List is missing', 400);
                }
        } else {
            return response('Unauthorized', 401);
        }
    }

    public function delete_row(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            if ($request->has("columnId") && $request->has("rowId")) {
                $columnId = $request->input("columnId");
                $column = KanbanColumn::with(['kanbanRows'])->where('id', $columnId)->where('userId', $user->id)->first();
                if (!$column) {
                    return response("Unauthorised", 401);
                }
                $rowId = $request->input("rowId");
                $row = KanbanRow::with(['kanbanColumn'])->where('id', $rowId)->where('kanbanColumnId', $columnId)->first();
                if (!$row) {
                    return response("Unauthorised", 401);
                }
                $row->delete();
                return response()->json($row, 200);
            } else {
                return response("columnId or rowId is missing", 400);
            }
        } else {
            return response("Unauthorised", 401);
        }
    }
}
