import type { APIContext } from "astro"

export async function get({}: APIContext) {
  return {
    body: 'export const search = () => {return {results: []}}'
  }
}
