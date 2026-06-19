import katex from 'katex'

/**
 * Parses and renders LaTeX formulas within HTML/text strings.
 * Replaces $...$ with inline math and $$...$$ with block math using KaTeX.
 *
 * @param {string} text - HTML or raw string containing LaTeX math formulas
 * @returns {string} Fully rendered HTML with styled math elements
 */
export function renderMath(text) {
  if (!text) return ''

  let rendered = text

  // 1. Render block math: $$...$$
  rendered = rendered.replace(/\$\$([^\$]+)\$\$/g, (match, formula) => {
    try {
      // Decode HTML entities if equations were saved with escaped entities
      const decodedFormula = formula
        .replace(/&lt;/g, '<')
        .replace(/&gt;/g, '>')
        .replace(/&amp;/g, '&')
      
      return katex.renderToString(decodedFormula, {
        throwOnError: false,
        displayMode: true
      })
    } catch (e) {
      console.warn('Failed to render block math:', formula, e)
      return match
    }
  })

  // 2. Render inline math: $...$
  rendered = rendered.replace(/\$([^\$]+)\$/g, (match, formula) => {
    try {
      const decodedFormula = formula
        .replace(/&lt;/g, '<')
        .replace(/&gt;/g, '>')
        .replace(/&amp;/g, '&')

      return katex.renderToString(decodedFormula, {
        throwOnError: false,
        displayMode: false
      })
    } catch (e) {
      console.warn('Failed to render inline math:', formula, e)
      return match
    }
  })

  return rendered
}
