import {
    pipeline
} from '@huggingface/transformers';


const MODEL =
    'onnx-community/SmolLM2-135M-Instruct-ONNX-MHA';


let generatorPromise =
    null;


async function loadGenerator() {

    if (!navigator.gpu) {

        throw new Error(
            'WebGPU is not available in this browser.'
        );
    }


    if (!generatorPromise) {

        generatorPromise =
            pipeline(
                'text-generation',
                MODEL,
                {
                    device:
                        'webgpu',

                    dtype:
                        'q4',

                    progress_callback:
                        progress => {

                            self.postMessage({
                                type:
                                    'progress',

                                progress
                            });
                        }
                }
            );

    }


    return generatorPromise;
}


function cleanSuggestion(
    generated,
    context
) {

    let suggestion =
        String(
            generated ?? ''
        )
            .replace(
                /\r/g,
                ''
            )
            .split('\n')
            .find(
                line =>
                    line.trim()
            )
            ?.trim()
        ?? '';


    /*
     * Remove quotation marks /
     * Markdown-style junk.
     */
    suggestion =
        suggestion
            .replace(
                /^["'`]+/,
                ''
            )
            .replace(
                /["'`]+$/,
                ''
            )
            .trim();


    /*
     * Keep autocomplete short.
     */
    suggestion =
        suggestion
            .split(/\s+/)
            .slice(
                0,
                12
            )
            .join(' ');


    if (!suggestion) {
        return '';
    }


    /*
     * If we're continuing directly
     * after a word, add spacing.
     */
    const lastCharacter =
        context.slice(-1);


    if (
        lastCharacter &&
        !/\s/.test(
            lastCharacter
        ) &&
        !/^[,.;:!?]/.test(
            suggestion
        )
    ) {

        suggestion =
            ' ' +
            suggestion;
    }


    return suggestion;
}


async function generateSuggestion(
    context
) {

    const generator =
        await loadGenerator();


    const messages = [

        {
            role:
                'system',

            content:
                `You are a local autocomplete engine for poetry and creative writing.

Continue the writer's text naturally.

Rules:
- return ONLY the continuation
- maximum 12 words
- never explain your answer
- never repeat the existing text
- no quotation marks
- no Markdown
- at most one line
- match the language and tone of the writer`
        },

        {
            role:
                'user',

            content:
                `Continue from the cursor:

${context}`
        }

    ];


    const output =
        await generator(
            messages,
            {
                max_new_tokens:
                    24,

                do_sample:
                    true,

                temperature:
                    0.8,

                top_p:
                    0.9,

                repetition_penalty:
                    1.1
            }
        );


    const generated =
        output?.[0]
            ?.generated_text
            ?.at(-1)
            ?.content
        ?? '';


    return cleanSuggestion(
        generated,
        context
    );
}


self.onmessage =
    async event => {

        const {
            type,
            requestId,
            context
        } = event.data;


        try {

            if (
                type ===
                'load'
            ) {

                await loadGenerator();


                self.postMessage({
                    type:
                        'ready'
                });


                return;
            }


            if (
                type ===
                'generate'
            ) {

                const suggestion =
                    await generateSuggestion(
                        context
                    );


                self.postMessage({
                    type:
                        'suggestion',

                    requestId,

                    context,

                    suggestion
                });
            }

        } catch (error) {

            self.postMessage({

                type:
                    'error',

                requestId,

                message:
                    error?.message
                    ??
                    'Local suggestion generation failed.'

            });
        }

    };