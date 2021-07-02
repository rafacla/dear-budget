<?php

class Controller
{
    /**
     * @OA\Info(
     *      version="1.0.0",
     *      title="Dear Budget API",
     *      description="API Documentation",
     *      @OA\Contact(
     *          email="rafacla at github.com"
     *      ),
     *      @OA\License(
     *          name="Apache 2.0",
     *          url="http://www.apache.org/licenses/LICENSE-2.0.html"
     *      )
     * )
     *
     * @OA\Server(
     *      url="/api/",
     *      description="Dear Budget API Server"
     * )
     *
     * @OAS\SecurityScheme(
     *      securityScheme="bearer_token",
     *      type="http",
     *      scheme="bearer"
     * )
     *
     * @OA\Tag(
     *     name="Accounts",
     *     description="API Endpoints of Accounts"
     * )
     */
}
