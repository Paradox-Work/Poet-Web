<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $userId =
            $request->user()?->id;

        $canDelete =
            $userId &&
            (
                $this->user_id === $userId
                ||
                (
                    $this->group &&
                    $this->group->isAdmin(
                        $userId
                    )
                )
            );

        $comments =
            $this->relationLoaded('comments')
                ? $this->comments
                : collect();

        $commentTree =
            self::convertCommentsIntoTree(
                $comments,
                $request
            );
            
        return [
            'id' => $this->id,
            'body' => $this->body,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
     
            'user' => new UserResource($this->user),
            'group' =>
                $this->group
                    ? new GroupResource(
                        $this->group
                    )
                    : null,

            'can_delete' =>
                $canDelete,
            'attachments' => PostAttachmentResource::collection(
                $this->attachments
            ),
            'num_of_reactions' =>
                $this->reactions_count ?? 0,

            'current_user_has_reaction' =>
                $this->relationLoaded('reactions')
                    && $this->reactions->isNotEmpty(),

            'num_of_comments' =>
                $comments->count(),

            'comments' =>
                $commentTree,

            'preview' =>
                $this->preview,

            'preview_url' =>
                $this->preview_url,
        ];
    }

    private static function convertCommentsIntoTree(
        Collection $comments,
        Request $request
    ): array {

        $commentsByParent = [];

        foreach ($comments as $comment) {

            $parentKey =
                $comment->parent_id === null
                    ? 'root'
                    : (string) $comment->parent_id;

            $commentsByParent[$parentKey][] =
                $comment;
        }


        $buildTree = function (
            int|string|null $parentId
        ) use (
            &$buildTree,
            $commentsByParent,
            $request
        ): array {

            $parentKey =
                $parentId === null
                    ? 'root'
                    : (string) $parentId;


            $tree = [];


            foreach (
                $commentsByParent[$parentKey] ?? []
                as $comment
            ) {

                $children =
                    $buildTree(
                        $comment->id
                    );


                $data =
                    (
                        new CommentResource(
                            $comment
                        )
                    )->resolve($request);


                $descendantCount =
                    count($children)
                    +
                    array_sum(
                        array_column(
                            $children,
                            'num_of_comments'
                        )
                    );


                $data['num_of_comments'] =
                    $descendantCount;

                $data['comments'] =
                    $children;


                $tree[] = $data;
            }


            return $tree;
        };


        return $buildTree(null);
    }
}