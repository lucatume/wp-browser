import rss from '@astrojs/rss';
import { getCollection } from 'astro:content';
import { SITE_DESCRIPTION, SITE_TITLE, SITE_URL } from '../site_config';

export async function get() {
  const blog = await getCollection('blog');
  return rss({
    title: SITE_TITLE,
    description: SITE_DESCRIPTION,
    site: SITE_URL,
    items: blog.sort((a, b) => b.data.published.getTime() - a.data.published.getTime()).map((post) => ({
      title: post.data.title,
      pubDate: post.data.published,
      description: post.data.description,
      link: `/blog/${post.slug}/`,
    })),
  });
}
