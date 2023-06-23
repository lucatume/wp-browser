import { z, defineCollection } from 'astro:content';

const v3 = defineCollection({
  schema: z.object({
    title: z.string(),
  }),
});

const docs = defineCollection({
  schema: z.object({
    title: z.string(),
  }),
});


export const collections = {
  v3,
  docs
};
