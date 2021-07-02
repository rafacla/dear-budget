<?php

namespace App\Http\Controllers\api;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    public $itemClass = Category::class;

    /**
     * @OA\Get(
     *      path="/categories",
     *      tags={"Categories"},
     *      summary="Get list of categories and subcategories",
     *      security={{"bearer_token":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *       ),
     *     )
     */
    public function index()
    {
        $categories = $this->itemClass::where('user_id', Auth::user()->id)->with('subcategories')->get();
        return $categories;
    }

    /**
     * @OA\Post(
     *      path="/categories",
     *      tags={"Categories"},
     *      summary="Create a new category",
     *      security={{"bearer_token":{}}},
     *     @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="multipart/form-data",
     *           @OA\Schema(
     *              @OA\Property(property="name", type="string"),
     *              @OA\Property(property="description", type="string"),
     *              @OA\Property(property="expense", type="bool"),
     *              required={"name", "expense"}
     *           ),
     *       )
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *       ),
     *     )
     */
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'name' => 'required',
            'expense' => 'required|boolean',
        ]);
        if ($validation->fails()) {
            return response('Missing parameters: ' . $validation->errors());
        } else {
            $request['user_id'] = Auth::user()->id;
            $items = $this->itemClass::where('user_id', $request['user_id'])->where('expense', $request['expense'])->get();
            $maxOrder = max(array_column($items->toArray(), 'order')) + 1 ?? 0;
            $request['order'] = $maxOrder;
            $items = $this->itemClass::where('user_id', $request['user_id'])->where('expense', $request['expense'])->where('name', $request['name'])->get();
            if (count($items) > 0) {
                return response('There\'s a category with this name already. Failed.', 403);
            } else {
                $item = $this->itemClass::create($request->all());
                if ($item) {
                    return response('Item created', 201);
                } else
                    return response('Failed', 500);
            }
        }
    }

    /**
     * @OA\Get(
     *      path="/categories/{categoryID}",
     *      tags={"Categories"},
     *      summary="Get a specific category",
     *      security={{"bearer_token":{}}},
     *      @OA\Parameter(
     *         in="path",
     *         name="categoryID",
     *         required=true,
     *         @OA\Schema(
     *           type="integer",
     *           format="int64"
     *         )
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *       ),
     *     )
     */
    public function show($id)
    {
        $item = $this->itemClass::findOrFail($id)->with('subcategories');

        if ($item->user_id != Auth::user()->id)
            abort(403, 'Unauthorized');
        else
            return $item;
    }

    /**
     * @OA\Put(
     *      path="/categories/{categoryID}",
     *      tags={"Categories"},
     *      summary="Update a category",
     *      security={{"bearer_token":{}}},
     *      @OA\Parameter(
     *         in="path",
     *         name="categoryID",
     *         required=true,
     *         @OA\Schema(
     *           type="integer",
     *           format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *              @OA\Property(property="name", type="string"),
     *              @OA\Property(property="description", type="string"),
     *              @OA\Property(property="expense", type="bool"),
     *              @OA\Property(property="order", type="number"),
     *           ),
     *       )
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *       ),
     *     )
     */
    public function update(Request $request, $id)
    {
        $item = $this->itemClass::findOrFail($id);

        if ($item->user_id != Auth::user()->id)  {
            return response('You can\'t change another user category. Failed.', 403);
        } else {
            if ($request['name'] != null) {
                $items = $this->itemClass::where('user_id', $item->user_id)
                    ->where('expense', $item->expense)
                    ->where('name', $request['name'])
                    ->where('id','!=', $id)
                    ->get();
            } else {
                $items = [];
            }
            if (count($items) > 0) {
                return response('There\'s a category with this name already. Failed.', 403);
            } else {
                $item = $item->update($request->all());
                if ($item) {
                    return response('Item updated', 200);
                } else
                    return response('Failed', 500);
            }
        }
    }

    /**
     * @OA\Delete(
     *      path="/categories/{categoryID}",
     *      tags={"Categories"},
     *      summary="Delete a specific item",
     *      security={{"bearer_token":{}}},
     *      @OA\Parameter(
     *         in="path",
     *         name="categoryID",
     *         required=true,
     *         @OA\Schema(
     *           type="integer",
     *           format="int64"
     *         )
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *       ),
     *     )
     */
    public function destroy($id)
    {
        $item = $this->itemClass::findOrFail($id);
        if ($item->user_id != Auth::user()->id)
            abort(403, 'Unauthorized');
        else
            $item->delete();
    }
}
