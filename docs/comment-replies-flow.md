## Single-query Comment Loading and PHP Tree Construction

### Purpose

The initial threaded-comment implementation recursively eager-loaded child comments through Eloquent.

Conceptually:

```text
Post
↓
root comments
↓
children
↓
children of children
↓
children of children of children
```

Although this correctly produced a nested structure, the database-loading strategy depended on the depth of the comment tree.

Poet-Web instead loads all comments belonging to the posts on the current page as one flat comment collection and constructs the hierarchical structure in PHP.

The architecture becomes:

```text
Database
↓
flat comment collection
↓
group comments by parent_id
↓
construct comment tree in PHP
↓
PostResource
↓
Vue CommentList
```

---

### Flat Database Structure

Replies do not use a separate table.

Every comment is stored in the `comments` table.

A top-level comment has:

```text
parent_id = NULL
```

A reply has:

```text
parent_id = ID of its direct parent comment
```

For example:

```text
id | parent_id | comment
---+-----------+-----------------------
1  | NULL      | Comment A
2  | NULL      | Comment B
3  | 1         | Reply C
4  | 1         | Reply D
5  | 3         | Reply E
```

represents:

```text
A
├── C
│   └── E
└── D

B
```

The database therefore stores a flat set of rows while `parent_id` describes the relationships between those rows.

---

### Loading the Comments

`HomeController` eager-loads all comments belonging to posts in the current paginated result.

The comment query also loads the information required by the frontend:

```text
comment author
reaction count
current user's reaction
```

The query does not restrict comments using:

```text
parent_id IS NULL
```

because both root comments and nested replies are required when building the complete tree.

The database therefore returns a flat collection similar to:

```text
A(parent_id = null)
B(parent_id = null)
C(parent_id = A)
D(parent_id = A)
E(parent_id = C)
```

---

### Why the Tree Is Built in PHP

The database relationships describe which comment belongs under another comment, but the frontend requires nested JSON.

The flat collection therefore needs to become:

```text
A
├── C
│   └── E
└── D

B
```

This transformation is performed inside `PostResource`.

The database is responsible for retrieving the rows.

PHP is responsible for arranging those rows into the structure expected by Vue.

---

### Grouping by `parent_id`

Before recursively constructing the tree, the comments are grouped according to their parent.

Given:

```text
A(parent = null)
B(parent = null)
C(parent = A)
D(parent = A)
E(parent = C)
```

the grouped lookup is approximately:

```text
root
├── A
└── B

A
├── C
└── D

C
└── E
```

Conceptually, the structure answers:

```text
Given a parent ID,
which comments belong directly underneath it?
```

This means PHP does not need to repeatedly search the complete comment collection to locate a comment's children.

---

### Performance Difference

A simpler recursive implementation can repeatedly scan the full comment collection.

Conceptually:

```php
foreach ($comments as $comment) {

    if ($comment->parent_id === $parentId) {

        findChildren($comment->id);

    }
}
```

Every recursive call searches the entire collection again.

With many nested comments, this can approach:

```text
O(n²)
```

behaviour.

For example, with approximately 1,000 comments, repeatedly scanning the same 1,000-element collection can result in a very large number of comparisons.

Poet-Web instead performs two main operations.

First:

```text
iterate over comments once
↓
group each comment by parent_id
```

Then:

```text
walk the grouped structure
↓
construct the nested tree
```

Each comment is grouped once and then processed while constructing the tree.

The resulting approach is approximately:

```text
O(n)
```

rather than repeatedly scanning the entire collection for each comment.

---

### Constructing the Tree

Tree construction begins with:

```text
parent_id = NULL
```

which identifies top-level comments.

For every top-level comment, PHP retrieves its children from the grouped lookup.

It then performs the same operation for each child.

Conceptually:

```text
buildTree(null)
│
├── A
│   └── buildTree(A)
│       ├── C
│       │   └── buildTree(C)
│       │       └── E
│       └── D
│
└── B
```

A comment without children produces:

```text
comments = []
```

which ends that recursive branch.

---

### Example Result

The flat database collection:

```text
A(parent = null)
B(parent = null)
C(parent = A)
D(parent = A)
E(parent = C)
```

becomes approximately:

```json
[
    {
        "id": "A",
        "comments": [
            {
                "id": "C",
                "comments": [
                    {
                        "id": "E",
                        "comments": []
                    }
                ]
            },
            {
                "id": "D",
                "comments": []
            }
        ]
    },
    {
        "id": "B",
        "comments": []
    }
]
```

The nested JSON can then be rendered recursively by `CommentList.vue`.

---

### Subcomments

A subcomment is not a different model or database entity.

It is a normal `Comment` whose `parent_id` points to another comment.

For example:

```text
Comment A
└── Reply B
    └── Reply C
```

is stored as:

```text
A.parent_id = NULL
B.parent_id = A.id
C.parent_id = B.id
```

A reply can therefore itself become a parent.

This allows comment nesting to continue without creating separate tables or components for each nesting depth.

---

### Recursive Vue Rendering

`PostItem.vue` starts the comment tree:

```vue
<CommentList
    :post="post"
    :comments="post.comments ?? []"
/>
```

`CommentList.vue` renders each comment.

When a comment contains replies, `CommentList.vue` invokes itself:

```vue
<CommentList
    :post="post"
    :comments="comment.comments ?? []"
    :parent-comment="comment"
/>
```

The rendering structure can therefore become:

```text
CommentList
├── Comment A
│   └── CommentList
│       └── Reply B
│           └── CommentList
│               └── Reply C
└── Comment D
```

The same component handles every depth of the comment tree.

---

### Descendant Counts

`num_of_comments` represents the total number of descendants below a comment.

For example:

```text
A
├── B
│   ├── C
│   └── D
└── E
```

produces:

```text
A = 4
B = 2
C = 0
D = 0
E = 0
```

The count for A includes:

```text
B
C
D
E
```

not only its immediate children.

While constructing the tree, PHP calculates:

```text
direct children
+
all descendants belonging to those children
```

This allows the interface to display the total size of a reply thread.

---

### Deleting Comment Trees

PHP tree construction does not delete comments.

Deletion is handled independently by the database relationship created for `parent_id`.

The foreign key uses cascading deletion:

```php
->constrained('comments')
->cascadeOnDelete();
```

Therefore, when a parent comment is deleted, comments that depend on it are also deleted.

For example:

```text
A
└── B
    └── C
        └── D
```

Deleting:

```text
B
```

causes the database to remove:

```text
B
C
D
```

while A remains.

The frontend calculates the size of the removed subtree as:

```text
1
+
comment.num_of_comments
```

The `1` represents the selected comment itself, while `num_of_comments` contains all descendants beneath it.

This allows ancestor reply counts to remain synchronized after a cascading deletion.

---

### Responsibility Separation

```text
Database
├── stores flat comment rows
├── stores parent_id relationships
└── enforces cascading deletion

HomeController
├── loads comments for the current posts
├── loads comment users
├── loads reaction counts
└── loads current-user reaction state

PostResource
├── groups comments by parent_id
├── constructs nested comment tree
└── calculates descendant counts

CommentResource
└── serializes individual comment information

CommentList.vue
├── recursively renders the tree
├── creates comments and replies
├── edits comments
├── deletes comments
├── updates ancestor counts
└── handles comment reactions
```

This keeps database retrieval, tree construction, API serialization, and frontend rendering as separate responsibilities.