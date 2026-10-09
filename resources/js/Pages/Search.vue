<script setup>
import {
    router
} from '@inertiajs/vue3';

import UserListItem
    from '@/Components/app/UserListItem.vue';

import GroupItem
    from '@/Components/app/GroupItem.vue';

import PostList
    from '@/Components/app/PostList.vue';

import CompactPaginator
    from '@/Components/app/CompactPaginator.vue';

import AuthenticatedLayout
    from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    users: Object,
    groups: Object,
    posts: Object,
    search: String
});

function goToPage(
    key,
    page
) {
    const query = {
        users_page:
            props.users.meta
                ?.current_page
                ?? 1,

        groups_page:
            props.groups.meta
                ?.current_page
                ?? 1
    };

    query[key] = page;

    router.get(
        route(
            'search',
            {
                search:
                    props.search
            }
        ),
        query,
        {
            preserveState: true,
            preserveScroll: true,
            replace: true
        }
    );
}
</script>

<template>
    <AuthenticatedLayout>
        <div
            class="h-full overflow-auto p-4"
        >
            <div
                class="mx-auto max-w-7xl"
            >
                <div
                    class="mb-5"
                >
                    <div
                        class="poet-kicker"
                    >
                        Search
                    </div>

                    <h1
                        class="poet-title mt-1 text-2xl"
                    >
                        Results for “{{ search }}”
                    </h1>
                </div>

                <div
                    v-if="
                        !search.startsWith('#')
                    "
                    class="grid grid-cols-1 gap-4 lg:grid-cols-2"
                >
                    <section
                        class="poet-card overflow-hidden"
                    >
                        <div
                            class="border-b border-[var(--poet-border)] px-4 py-3"
                        >
                            <h2
                                class="font-serif text-lg font-semibold"
                            >
                                Users
                            </h2>
                        </div>

                        <div
                            class="p-3"
                        >
                            <UserListItem
                                v-for="user in users.data"
                                :key="user.id"
                                :user="user"
                            />

                            <div
                                v-if="
                                    !users.data.length
                                "
                                class="py-8 text-center text-sm text-[var(--poet-muted)]"
                            >
                                No users were found.
                            </div>
                        </div>

                        <div
                            v-if="
                                users.meta
                                    ?.last_page > 1
                            "
                            class="border-t border-[var(--poet-border)] p-3"
                        >
                            <CompactPaginator
                                :meta="users.meta"
                                label="User search pages"
                                @page="
                                    goToPage(
                                        'users_page',
                                        $event
                                    )
                                "
                            />
                        </div>
                    </section>

                    <section
                        class="poet-card overflow-hidden"
                    >
                        <div
                            class="border-b border-[var(--poet-border)] px-4 py-3"
                        >
                            <h2
                                class="font-serif text-lg font-semibold"
                            >
                                Groups
                            </h2>
                        </div>

                        <div
                            class="p-3"
                        >
                            <GroupItem
                                v-for="group in groups.data"
                                :key="group.id"
                                :group="group"
                            />

                            <div
                                v-if="
                                    !groups.data.length
                                "
                                class="py-8 text-center text-sm text-[var(--poet-muted)]"
                            >
                                No groups were found.
                            </div>
                        </div>

                        <div
                            v-if="
                                groups.meta
                                    ?.last_page > 1
                            "
                            class="border-t border-[var(--poet-border)] p-3"
                        >
                            <CompactPaginator
                                :meta="groups.meta"
                                label="Group search pages"
                                @page="
                                    goToPage(
                                        'groups_page',
                                        $event
                                    )
                                "
                            />
                        </div>
                    </section>
                </div>

                <section
                    class="mt-5"
                >
                    <h2
                        class="mb-2 font-serif text-lg font-semibold"
                    >
                        Publications
                    </h2>

                    <PostList
                        v-if="posts.data.length"
                        :posts="posts"
                        class="flex-1"
                    />

                    <div
                        v-else
                        class="poet-card py-10 text-center text-sm text-[var(--poet-muted)]"
                    >
                        No publications were found.
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
