# ![FAQzin Logo](assets/images/logo.png) FAQzin

**Professional FAQ Manager for WordPress**

[![License](https://img.shields.io/badge/License-GPLv2%20or%20later-blue.svg)](http://www.gnu.org/licenses/gpl-2.0.html)
[![Version](https://img.shields.io/badge/Version-2.0.0-green.svg)](https://vizuh.com)

---

## 🌐 Documentation / Documentação

- [🇺🇸 English Documentation](#english-documentation)
- [🇧🇷 Documentação em Português](#documentação-em-português)

---

<a name="english-documentation"></a>
## 🇺🇸 English Documentation

**FAQzin** is a lightweight, professional FAQ plugin designed for modern WordPress sites. It features native HTML5 accordions, automatic SEO schema markup, and a premium design out of the box.

### Features
- **HTML5 Native:** Built with `<details>` and `<summary>` for accessibility and performance.
- **SEO Ready:** Automatically injects `FAQPage` and `QAPage` JSON-LD schema.
- **Responsive:** Looks great on mobile, tablet, and desktop.
- **Customizable:** Add your own CSS directly from the settings page.
- **Headless ready:** With WPGraphQL, FAQs and categories are in the GraphQL schema; a headless mode turns off FAQ pages on WordPress.
- **Featured FAQs and category order:** Mark FAQs as Featured and give each category an Order.
- **WP-CLI import:** `wp faqzin import faqs.json` creates categories and FAQs from a JSON file.

### Installation
1. Download the plugin zip file.
2. Go to **Plugins > Add New > Upload Plugin** in WordPress.
3. Upload and activate.

### Usage
Use the shortcode `[faqzin]` to display all FAQs.

**Parameters:**
- `category`: Filter by category slug. Example: `[faqzin category="sales"]`
- `limit`: Limit the number of items. Example: `[faqzin limit="5"]`
- `schema`: Enable/Disable schema (default: yes). Example: `[faqzin schema="no"]`

### Headless sites (WPGraphQL)
When another site (Next.js, Astro, …) shows the FAQs and WordPress only edits them:

1. Install and activate [WPGraphQL](https://wordpress.org/plugins/wp-graphql/).
2. Tick **FAQs > Settings > Headless mode**. WordPress then publishes no FAQ pages, category archives or FAQ schema (the other site renders those); FAQs stay available over GraphQL.

```graphql
{
  faqs(first: 100, where: { orderby: [{ field: MENU_ORDER, order: ASC }] }) {
    nodes {
      slug
      title          # the question
      answerText     # the answer as plain text, a blank line between paragraphs
      content        # the answer as HTML
      featured
      faqCategories { nodes { slug name order } }
    }
  }
}
```

`featured` is the **Featured** box on each FAQ; `order` is the **Order** field on each FAQ category (null when empty).

### WP-CLI import
`wp faqzin import faqs.json [--overwrite]` creates categories and FAQs matched by slug; existing ones are skipped unless `--overwrite`. The format is in `wp help faqzin import`:

```json
{
  "categories": [{ "slug": "general", "name": "General", "order": 1 }],
  "faqs": [{
    "slug": "opening-hours", "question": "When are you open?",
    "answer": "First paragraph.\n\nSecond paragraph.",
    "category": "general", "order": 1, "featured": true, "status": "publish"
  }]
}
```

---

<a name="documentação-em-português"></a>
## 🇧🇷 Documentação em Português

**FAQzin** é um plugin de FAQ profissional e leve, projetado para sites WordPress modernos. Ele utiliza accordions nativos HTML5, marcação de esquema SEO automática e um design premium pronto para uso.

### Funcionalidades
- **Nativo HTML5:** Construído com `<details>` e `<summary>` para acessibilidade e performance.
- **Pronto para SEO:** Injeta automaticamente schema JSON-LD `FAQPage` e `QAPage`.
- **Responsivo:** Visual perfeito em celulares, tablets e desktops.
- **Personalizável:** Adicione seu próprio CSS diretamente na página de configurações.
- **Pronto para headless:** Com o WPGraphQL, FAQs e categorias ficam no schema GraphQL; o modo headless desliga as páginas de FAQ no WordPress.
- **FAQs em destaque e ordem das categorias:** Marque FAQs como Destaque (Featured) e defina a Ordem de cada categoria.
- **Importação via WP-CLI:** `wp faqzin import faqs.json` cria categorias e FAQs a partir de um arquivo JSON.

### Instalação
1. Baixe o arquivo zip do plugin.
2. Vá em **Plugins > Adicionar Novo > Enviar Plugin** no WordPress.
3. Envie e ative.

### Como Usar
Use o shortcode `[faqzin]` para exibir todos os FAQs.

**Parâmetros:**
- `category`: Filtrar por slug da categoria. Exemplo: `[faqzin category="vendas"]`
- `limit`: Limitar o número de itens. Exemplo: `[faqzin limit="5"]`
- `schema`: Ativar/Desativar schema (padrão: yes). Exemplo: `[faqzin schema="no"]`

### Sites headless (WPGraphQL)
Quando outro site (Next.js, Astro, …) exibe os FAQs e o WordPress só os edita:

1. Instale e ative o [WPGraphQL](https://wordpress.org/plugins/wp-graphql/).
2. Marque **FAQs > Settings > Headless mode**. O WordPress deixa de publicar páginas de FAQ, arquivos de categoria e schema de FAQ (o outro site cuida disso); os FAQs continuam disponíveis via GraphQL: `faqs` com `answerText` (resposta em texto puro, linha em branco entre parágrafos), `content` (HTML) e `featured`, e `faqCategories` com `order`.

### Importação via WP-CLI
`wp faqzin import faqs.json [--overwrite]` cria categorias e FAQs pelo slug; os existentes são mantidos, a menos que se use `--overwrite`. O formato está em `wp help faqzin import` e no exemplo em inglês acima.

---

### Support / Suporte

For support, please visit [vizuh.com](https://vizuh.com).

Developed by **Vizuh**.
