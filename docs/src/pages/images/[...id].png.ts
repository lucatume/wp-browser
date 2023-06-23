import satori from 'satori';
import { html } from 'satori-html';
import OpenSans from '../../lib/OpenSans-Regular.ttf'
import type { APIContext } from 'astro';
import { Resvg } from '@resvg/resvg-js';
import { CollectionEntry, getCollection, getEntryBySlug } from 'astro:content';
import { SITE_DESCRIPTION, SITE_TITLE } from '../../site_config';

const height = 630;
const width = 1200;

export async function get({ url, params, props }: APIContext) {
  const { id } = params;
  const { collection } = props as { collection: 'blog' | 'docs' };

  let post: CollectionEntry<'blog'> | CollectionEntry<'docs'> | undefined;

  try {
    if (id === 'default') {
      post = {
        data: {
          title: SITE_TITLE,
          description: SITE_DESCRIPTION,
          published: new Date()
        }
      } as CollectionEntry<'blog'>;
    } else if (id) {
      post = await getEntryBySlug(collection, (id.split('.md')[0]));
    }
  } catch {
    return {
      status: 404,
      body: 'Not Found'
    }
  }

  const out = html`<div tw="flex flex-col w-full h-full bg-white">
    <span tw="absolute top-0 left-0 p-2 w-full h-full rounded-2xl">
      <span tw="w-full h-full rounded-2xl bg-white"></span>
    </span>
    <span tw="absolute top-12 left-24 w-[56rem] text-[5rem] flex flex-col">
      <h1>${post?.data.title}</h1>
      <p tw="text-[1.8rem] w-[56rem] bottom-32">${(post?.data as any).description ?? ''}</p>
    </span>
    <p tw="absolute bottom-12 left-24 text-[1.5rem] text-zinc-600">${(post?.data as any).published?.toDateString() ?? ''}</p>
  </div>`

  let svg = await satori(out, {
    fonts: [
      {
        name: 'Open Sans',
        data: Buffer.from(OpenSans),
        style: 'normal'
      }
    ],
    height,
    width
  });

  const resvg = new Resvg(svg, {
    fitTo: {
      mode: 'width',
      value: width
    }
  });

  const image = resvg.render();

  return {
    headers: {
      'Content-Type': 'image/png',
      'Cache-Control': 'public, max-age=31536000, immutable'
    },

    body: image.asPng()
  }
};

export async function getStaticPaths() {
  return (await getCollection('blog')).map((post) => ({
    params: { id: post.id },
    props: { collection: 'blog' }
  }) as any).concat((await getCollection('docs')).map((post) => ({
    params: { id: post.id },
    props: { collection: 'docs' }
  }))).concat({
    params: { id: 'default' }
  });
}
