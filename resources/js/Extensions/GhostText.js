import {
    Extension
} from '@tiptap/core';

import {
    Plugin,
    PluginKey
} from '@tiptap/pm/state';

import {
    Decoration,
    DecorationSet
} from '@tiptap/pm/view';


export const ghostTextPluginKey =
    new PluginKey('ghostText');


const emptySuggestion = {
    text: '',
    position: null
};


export default Extension.create({

    name: 'ghostText',


    addCommands() {

        return {

            setGhostText:
                text =>
                ({
                    state,
                    dispatch
                }) => {

                    if (!dispatch) {
                        return true;
                    }


                    dispatch(
                        state.tr.setMeta(
                            ghostTextPluginKey,
                            {
                                type: 'set',

                                text,

                                position:
                                    state.selection.from
                            }
                        )
                    );


                    return true;
                },


            clearGhostText:
                () =>
                ({
                    state,
                    dispatch
                }) => {

                    if (!dispatch) {
                        return true;
                    }


                    dispatch(
                        state.tr.setMeta(
                            ghostTextPluginKey,
                            {
                                type: 'clear'
                            }
                        )
                    );


                    return true;
                }

        };
    },


    addProseMirrorPlugins() {

        return [

            new Plugin({

                key:
                    ghostTextPluginKey,


                state: {

                    init() {

                        return {
                            ...emptySuggestion
                        };
                    },


                    apply(
                        transaction,
                        previous
                    ) {

                        const meta =
                            transaction.getMeta(
                                ghostTextPluginKey
                            );


                        if (
                            meta?.type ===
                            'set'
                        ) {

                            return {
                                text:
                                    meta.text,

                                position:
                                    meta.position
                            };
                        }


                        if (
                            meta?.type ===
                            'clear'
                        ) {

                            return {
                                ...emptySuggestion
                            };
                        }


                        /*
                         * Any real edit or cursor
                         * movement invalidates the
                         * previous suggestion.
                         */
                        if (
                            transaction.docChanged ||
                            transaction.selectionSet
                        ) {

                            return {
                                ...emptySuggestion
                            };
                        }


                        return previous;
                    }

                },


                props: {

                    decorations(state) {

                        const suggestion =
                            ghostTextPluginKey
                                .getState(state);


                        if (
                            !suggestion?.text ||
                            suggestion.position ===
                                null ||
                            !state.selection.empty ||
                            state.selection.from !==
                                suggestion.position
                        ) {

                            return null;
                        }


                        const decoration =
                            Decoration.widget(

                                suggestion.position,

                                () => {

                                    const span =
                                        document
                                            .createElement(
                                                'span'
                                            );


                                    span.className =
                                        'ghost-text-suggestion';


                                    span.textContent =
                                        suggestion.text;


                                    return span;
                                },

                                {
                                    side: 1,

                                    key:
                                        'ghost-text'
                                }
                            );


                        return DecorationSet.create(
                            state.doc,
                            [
                                decoration
                            ]
                        );
                    },


                    handleKeyDown(
                        view,
                        event
                    ) {

                        const suggestion =
                            ghostTextPluginKey
                                .getState(
                                    view.state
                                );


                        if (
                            !suggestion?.text
                        ) {

                            return false;
                        }


                        /*
                         * TAB
                         * Accept suggestion.
                         */
                        if (
                            event.key ===
                            'Tab'
                        ) {

                            event.preventDefault();


                            const position =
                                view.state
                                    .selection
                                    .from;


                            const transaction =
                                view.state.tr
                                    .insertText(
                                        suggestion.text,
                                        position
                                    )
                                    .setMeta(
                                        ghostTextPluginKey,
                                        {
                                            type:
                                                'clear'
                                        }
                                    );


                            view.dispatch(
                                transaction
                            );


                            return true;
                        }


                        /*
                         * ESC
                         * Reject suggestion.
                         */
                        if (
                            event.key ===
                            'Escape'
                        ) {

                            event.preventDefault();


                            view.dispatch(
                                view.state.tr
                                    .setMeta(
                                        ghostTextPluginKey,
                                        {
                                            type:
                                                'clear'
                                        }
                                    )
                            );


                            return true;
                        }


                        return false;
                    }

                }

            })

        ];
    }

});