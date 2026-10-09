<script setup>
import {
    computed
} from 'vue';

import {
    router,
    usePage
} from '@inertiajs/vue3';

import {
    BellIcon,
    CheckIcon,
    XMarkIcon
} from '@heroicons/vue/24/outline';

import Dropdown
    from '@/Components/Dropdown.vue';

const page = usePage();

const notificationCenter =
    computed(() =>
        page.props.notifications ?? {
            unread_count: 0,
            items: []
        }
    );

const unreadCount =
    computed(() =>
        Number(
            notificationCenter
                .value
                .unread_count
                ?? 0
        )
    );

const items =
    computed(() =>
        notificationCenter
            .value
            .items
            ?? []
    );

function relativeTime(value) {
    if (!value) {
        return '';
    }

    const date =
        new Date(value);

    const seconds =
        Math.round(
            (
                date.getTime() -
                Date.now()
            ) / 1000
        );

    const formatter =
        new Intl.RelativeTimeFormat(
            undefined,
            {
                numeric: 'auto'
            }
        );

    const divisions = [
        {
            amount: 60,
            name: 'second'
        },
        {
            amount: 60,
            name: 'minute'
        },
        {
            amount: 24,
            name: 'hour'
        },
        {
            amount: 7,
            name: 'day'
        },
        {
            amount: 4.34524,
            name: 'week'
        },
        {
            amount: 12,
            name: 'month'
        },
        {
            amount: Infinity,
            name: 'year'
        }
    ];

    let duration =
        seconds;

    for (
        const division of divisions
    ) {
        if (
            Math.abs(duration) <
            division.amount
        ) {
            return formatter.format(
                Math.round(duration),
                division.name
            );
        }

        duration /=
            division.amount;
    }

    return '';
}

function markRead(
    notification,
    navigate = true
) {
    const finish = () => {
        if (
            navigate &&
            notification.action_url
        ) {
            router.visit(
                notification.action_url
            );
        }
    };

    if (notification.read_at) {
        finish();
        return;
    }

    router.post(
        route(
            'notifications.read',
            notification.id
        ),
        {},
        {
            preserveScroll: true,
            preserveState: true,
            onSuccess: finish
        }
    );
}

function markAllRead() {
    if (!unreadCount.value) {
        return;
    }

    router.post(
        route(
            'notifications.readAll'
        ),
        {},
        {
            preserveScroll: true,
            preserveState: true
        }
    );
}

function acceptInvitation(
    notification
) {
    if (!notification.accept_url) {
        return;
    }

    router.post(
        notification.accept_url,
        {},
        {
            preserveScroll: true,
            preserveState: true
        }
    );
}

function declineInvitation(
    notification
) {
    if (!notification.decline_url) {
        return;
    }

    router.post(
        notification.decline_url,
        {},
        {
            preserveScroll: true,
            preserveState: true
        }
    );
}
</script>

<template>
    <Dropdown
        align="right"
        width="notifications"
        content-classes="bg-[var(--poet-surface)]"
    >
        <template #trigger>
            <button
                type="button"
                class="poet-focus relative inline-flex h-9 w-9 items-center justify-center rounded-full text-[var(--poet-muted)] transition hover:bg-[var(--poet-surface-soft)] hover:text-[var(--poet-text)]"
                aria-label="Notifications"
            >
                <BellIcon
                    class="h-5 w-5"
                />

                <span
                    v-if="unreadCount"
                    class="absolute -right-0.5 -top-0.5 flex min-h-4 min-w-4 items-center justify-center rounded-full bg-[var(--poet-accent)] px-1 text-[9px] font-bold leading-none text-white ring-2 ring-[var(--poet-nav)]"
                >
                    {{
                        unreadCount > 99
                            ? '99+'
                            : unreadCount
                    }}
                </span>
            </button>
        </template>

        <template #content>
            <div
                class="flex items-center justify-between gap-3 border-b border-[var(--poet-border)] px-4 py-3"
            >
                <div>
                    <div
                        class="font-serif text-base font-semibold text-[var(--poet-text)]"
                    >
                        Notifications
                    </div>

                    <div
                        class="text-[11px] text-[var(--poet-muted)]"
                    >
                        {{
                            unreadCount
                                ? unreadCount +
                                  ' unread'
                                : 'You are caught up'
                        }}
                    </div>
                </div>

                <button
                    v-if="unreadCount"
                    type="button"
                    @click.stop="markAllRead"
                    class="text-xs font-medium text-[var(--poet-accent-strong)] transition hover:text-[var(--poet-accent)]"
                >
                    Mark all read
                </button>
            </div>

            <div
                v-if="items.length"
                class="max-h-[28rem] overflow-y-auto"
            >
                <article
                    v-for="notification in items"
                    :key="notification.id"
                    :class="[
                        'border-b border-[var(--poet-border)] px-4 py-3 last:border-b-0',
                        notification.read_at
                            ? 'bg-[var(--poet-surface)]'
                            : 'bg-[var(--poet-accent-soft)]/35'
                    ]"
                >
                    <button
                        v-if="
                            notification.kind !==
                            'group_invitation'
                        "
                        type="button"
                        @click.stop="
                            markRead(
                                notification
                            )
                        "
                        class="block w-full text-left"
                    >
                        <div
                            class="flex items-start gap-3"
                        >
                            <span
                                :class="[
                                    'mt-1 h-2 w-2 shrink-0 rounded-full',
                                    notification.read_at
                                        ? 'bg-transparent'
                                        : 'bg-[var(--poet-accent)]'
                                ]"
                            />

                            <span
                                class="min-w-0 flex-1"
                            >
                                <span
                                    class="block text-sm font-semibold text-[var(--poet-text)]"
                                >
                                    {{
                                        notification.title
                                    }}
                                </span>

                                <span
                                    class="mt-0.5 block text-xs leading-5 text-[var(--poet-muted)]"
                                >
                                    {{
                                        notification.message
                                    }}
                                </span>

                                <span
                                    class="mt-1 block text-[10px] text-[var(--poet-muted)]"
                                >
                                    {{
                                        relativeTime(
                                            notification.created_at
                                        )
                                    }}
                                </span>
                            </span>
                        </div>
                    </button>

                    <div
                        v-else
                        class="flex items-start gap-3"
                    >
                        <span
                            :class="[
                                'mt-1 h-2 w-2 shrink-0 rounded-full',
                                notification.read_at
                                    ? 'bg-transparent'
                                    : 'bg-[var(--poet-accent)]'
                            ]"
                        />

                        <div
                            class="min-w-0 flex-1"
                        >
                            <div
                                class="text-sm font-semibold text-[var(--poet-text)]"
                            >
                                {{
                                    notification.title
                                }}
                            </div>

                            <p
                                class="mt-0.5 text-xs leading-5 text-[var(--poet-muted)]"
                            >
                                {{
                                    notification.message
                                }}
                            </p>

                            <div
                                class="mt-2 flex items-center gap-2"
                            >
                                <button
                                    type="button"
                                    @click.stop="
                                        acceptInvitation(
                                            notification
                                        )
                                    "
                                    class="inline-flex items-center gap-1.5 rounded-full bg-[var(--poet-accent)] px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-[var(--poet-accent-strong)]"
                                >
                                    <CheckIcon
                                        class="h-3.5 w-3.5"
                                    />
                                    Accept
                                </button>

                                <button
                                    type="button"
                                    @click.stop="
                                        declineInvitation(
                                            notification
                                        )
                                    "
                                    class="inline-flex items-center gap-1.5 rounded-full border border-[var(--poet-border)] px-3 py-1.5 text-xs font-medium text-[var(--poet-muted)] transition hover:bg-[var(--poet-surface-soft)] hover:text-[var(--poet-text)]"
                                >
                                    <XMarkIcon
                                        class="h-3.5 w-3.5"
                                    />
                                    Decline
                                </button>
                            </div>

                            <div
                                class="mt-1.5 text-[10px] text-[var(--poet-muted)]"
                            >
                                {{
                                    relativeTime(
                                        notification.created_at
                                    )
                                }}
                            </div>
                        </div>
                    </div>
                </article>
            </div>

            <div
                v-else
                class="px-6 py-8 text-center"
            >
                <BellIcon
                    class="mx-auto h-6 w-6 text-[var(--poet-muted)]"
                />

                <div
                    class="mt-2 text-sm font-medium text-[var(--poet-text)]"
                >
                    Nothing new.
                </div>

                <p
                    class="mt-1 text-xs text-[var(--poet-muted)]"
                >
                    Group invitations and updates will appear here.
                </p>
            </div>
        </template>
    </Dropdown>
</template>
