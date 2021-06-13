<?php

namespace App\Http\Controllers\api;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subcategory;

class SubcategoryController extends Controller
{
    public $itemClass = Subcategory::class;

    /**
     * @OA\Get(
     *      path="/subcategories",
     *      tags={"Categories"},
     *      summary="Get list of subcategories",
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
        $items = $this->itemClass::whereHas('category', function ($builder) {
            $builder->where('user_id', Auth::user()->id);
        })->get();
        return $items;
    }

    /**
     * @OA\Post(
     *      path="/subcategories",
     *      tags={"Categories"},
     *      summary="Create a new category",
     *      security={{"bearer_token":{}}},
     *     @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="multipart/form-data",
     *           @OA\Schema(
     *              @OA\Property(property="name", type="string"),
     *              @OA\Property(property="description", type="string"),
     *              @OA\Property(property="category_id", type="number"),
     *              required={"name", "category_id"}
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
            'category_id' => 'required|boolean',
        ]);
        if ($validation->fails()) {
            return response('Missing parameters: ' . $validation->errors());
        } else {
            $request['user_id'] = Auth::user()->id;
            $items = $this->itemClass::where('category_id', $request['category_id'])->get();
            $maxOrder = max(array_column($items->toArray(), 'order')) + 1 ?? 0;
            $request['order'] = $maxOrder;
            $items = $this->itemClass::where('category_id', $request['category_id'])->where('name', $request['name'])->get();
            if (count($items) > 0) {
                return response('There\'s a subcategory in this category with this name already. Failed.', 403);
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
     *      path="/subcategories/{subcategoryID}",
     *      tags={"Categories"},
     *      summary="Get a specific subcategory",
     *      security={{"bearer_token":{}}},
     *      @OA\Parameter(
     *         in="path",
     *         name="subcategoryID",
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
        $item = $this->itemClass::findOrFail($id);

        if ($item->category->user_id != Auth::user()->id)
            return response('Unauthorized', 403);
        else
            return $item;
    }

    /**
     * @OA\Put(
     *      path="/subcategories/{subcategoryID}",
     *      tags={"Categories"},
     *      summary="Update a subcategory",
     *      security={{"bearer_token":{}}},
     *      @OA\Parameter(
     *         in="path",
     *         name="subcategoryID",
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
     *              @OA\Property(property="category_id", type="number"),
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

        if ($item->category->user_id != Auth::user()->id) {
            return response('You can\'t change another user subcategory. Failed.', 403);
        } else {
            if ($request['name'] != null) {
                $items = $this->itemClass::where('category_id', $request['category_id'] ?? $item->category->id)
                    ->where('name', $request['name'])
                    ->where('id', '!=', $id)
                    ->get();
            } else {
                $items = [];
            }
            if (count($items) > 0) {
                return response('There\'s a subcategory with this name in this category already. Failed.', 403);
            } else {
                if ($request['category_id'] != null) {
                    $oldCategoryId = $item->category_id;
                    if ($request['order'] == null) {
                        //we are changing this subcategory category and we are not defining a new order
                        //therefore we must find a new order for it:
                        $items = $this->itemClass::where('category_id', $request['category_id'])->get();
                        $maxOrder = max(array_column($items->toArray(), 'order')) + 1 ?? 0;
                        $request['order'] = $maxOrder;
                    }
                    //this is breaking the old category order, right?
                    //Let's do a quick fix:
                    $oldCategoryItems = $this->itemClass::where('category_id', $oldCategoryId)->orderBy('order')->get();
                    $oldCategoryItemOrder = 0;
                    foreach ($oldCategoryItems as $oldCategoryItem) {
                        if ($oldCategoryItem->id != $item->id)
                            $oldCategoryItem->update(['order' => $oldCategoryItemOrder++]);
                    }
                }
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
     *      path="/subcategories/{subcategoryID}",
     *      tags={"Categories"},
     *      summary="Delete a specific item",
     *      security={{"bearer_token":{}}},
     *      @OA\Parameter(
     *         in="path",
     *         name="subcategoryID",
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
        if ($item->category->user_id != Auth::user()->id)
            abort(403, 'Unauthorized');
        else
            $item->delete();
    }
}
