=== Theme Builder For Elementor ===
Contributors: theblockswp
Tags: elementor, theme builder, header footer, templates, archive
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 2.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Design the header, footer, posts, archives, search and 404 page of any theme with the free Elementor – with display conditions and ready templates.

== Description ==

**Theme Builder For Elementor** lets you design every part of your site with the free Elementor – no Elementor Pro needed. Make a header, a footer, the layout of your posts and pages, the blog and category archives, the search results and the 404 page, choose where each one is shown, and the plugin puts it in place of the parts of your theme.

[Live demos](https://demos.blocks-wp.com/) · [Documentation](https://blocks-wp.com/docs/theme-builder-for-elementor/)

= How it works =

1. Go to **Theme Builder › Templates** and click **New template** – or start from a ready-made design in the **Template Library**.
2. Design it in Elementor with the widgets of the plugin: site logo, navigation menu, post title, posts grid and many more.
3. Set its **display conditions** – the entire site, all posts, one category, the blog, search results… – and publish.

= Templates for every part of the site =

* **Header and footer** – for the whole site or only for some pages
* **Single** – the layout of posts, pages and custom post types
* **Archive** – the blog, categories, tags, authors and dates
* **Search results** and the **404 page**
* **Post Card** – your own design of one post in a list, used by the Posts widget
* **Section** – reusable parts such as a sidebar, a call to action or the slides of a slider

= Display conditions =

Choose where each template is shown: the entire site, all posts, posts of a category or tag, posts by an author, single pages, the front page, the blog, archives, search results or the 404 page. Add exceptions with **Exclude**. When several templates match a page, the most specific one wins. Every post and page can also pick its own header, footer and layout in the editor.

= Widgets and dynamic tags =

29 widgets, shown first in the Elementor panel when you edit a template: post title, content, featured image, date, author, categories, tags, comments count, post info with the reading time, read more, post navigation, author box, comments, breadcrumbs, archive title with the number of results, posts (grid or list with pagination and related posts), site logo, site title, navigation menu with a mobile menu, search form (also as an icon), copyright, share buttons, table of contents, reading progress, back to top, advanced heading with an animated middle part, slider (every slide is a section template), section template and widget area.

22 dynamic tags put the post title, excerpt, date, terms, reading time, author, archive title, site title, a custom field, links and images into any Elementor widget – also with the free Elementor.

= Archives with your own post cards =

The Posts widget follows the current query, so one archive template shows the right posts on every category, tag, author or search page. Design a **Post Card** template in any layout – picture on the left, title over the image, a category badge – and every post in the list is shown with it.

= Header behaviour =

Make the header sticky, shrink it on scroll, hide it while the visitor scrolls down, or lay it transparently over the top of the page – on all devices or only on desktop.

= Template Library and Websites Import =

Start from a finished design instead of an empty page:

* **Template Library** – ready-made headers, footers, single posts, archives, search and 404 pages, post cards and sections. Preview them live and import them as drafts in one click, or insert a section right in the Elementor editor.
* **Websites Import** – complete websites. Filter them by industry, Free or PRO, open one, pick the pages you need and import them with the header, the footer and the layouts of posts and archives.
* **Your colors** – keep the colors of a demo or choose a palette or your own colors in a live preview before the import, optionally as the global colors of Elementor.
* **New designs** appear without a plugin update, with a short notice in the dashboard showing the newest ones.

Pictures of an imported design are copied into your Media Library, so your site never depends on ours.

= Works with any theme =

Headers, footers and layouts replace the parts of the theme in classic themes (Astra, GeneratePress, OceanWP, Kadence, Blocksy, Neve, Hello Elementor…) and in block themes (Twenty Twenty-Five, Twenty Twenty-Four…). Pages built with Elementor can use the Canvas or Full width layout of the plugin.

= Theme Builder PRO =

[Theme Builder PRO](https://blocks-wp.com/pricing/) adds the WooCommerce shop – single product, product archive, cart, checkout, My account and order received templates with 30+ shop widgets – popups and mega menus, and all PRO designs of the library. The PRO template types are shown locked in the free plugin, so you can see them before you buy.

= Source code =

The administration of the plugin (build/admin.js, build/admin.css) is built from the React sources in the src/ folder of the plugin with @wordpress/scripts (`npm install`, then `npm run build`). The other files of the plugin are not compiled; the .min.css and .min.js files are minified copies of the files next to them.

== External services ==

The Template Library and Websites Import load their designs from our demo site demos.blocks-wp.com (https://demos.blocks-wp.com/):

* The list of the templates and websites is loaded once a day (in the background with WP-Cron, or when you open the Template Library or Websites Import). You can refresh it by hand at most every 6 hours. Without a connection the last loaded list is used.
* A demo file is downloaded only when you import it, and it is checked against the checksum of the list.
* The preview images are loaded from the demo site; after they were shown once, a copy is kept in wp-content/uploads/assets/tbfe-previews/ and served by your site.
* Images of an imported demo are downloaded into your media library.

The requests carry only the version of the plugin, not the address of your site or any data of its users (like every request, they come from the IP address of your server). You can turn the library off in Theme Builder › Settings.

Terms and conditions: https://blocks-wp.com/terms-and-conditions/ · Privacy policy: https://blocks-wp.com/privacy-policy/

== Installation ==

1. Install and activate **Elementor** (the free version is enough).
2. Go to Plugins › Add New, search for **Theme Builder For Elementor**, install and activate it.
3. Open **Theme Builder**: create a template or import one from the Template Library, design it with Elementor and set its display conditions. Step-by-step guides: [Getting started](https://blocks-wp.com/docs/theme-builder-for-elementor/getting-started/).

== Frequently Asked Questions ==

= Is Elementor Pro required? =

No. The plugin is built for the free Elementor. Its widgets and dynamic tags work without Elementor Pro.

= Does it work with my theme? =

Yes. It replaces the header, footer and content of classic themes and the template parts of block themes. If a template looks different on the site than in the editor, Theme Builder › Settings › Tools › Regenerate CSS helps; more in the [troubleshooting guide](https://blocks-wp.com/docs/theme-builder-for-elementor/troubleshooting/).

= How do I make the blog and category pages? =

Create an **Archive** template with the Archive Title and Posts widgets. Set the Posts widget to **Current query**, so it shows the posts of the category, tag, author or search the visitor opened, and choose where the template is shown in the display conditions.

= How do I change how one post looks in the list? =

Create a **Post Card** template, design one post with the Post Image, Post Title, Post Date and Read More widgets, and choose it as the **Card template** of the Posts widget. Every post in the list is shown with your card.

= Will an import change my existing pages? =

No. Templates are imported as drafts and websites as new pages. An existing page is changed only when you import a design into it from its editor, and you are asked first.

= Does it work without an internet connection? =

Your templates do. The Template Library and Websites Import need our demo site (see "External services") and can be turned off in Theme Builder › Settings.

= I used version 1. What happens with my templates? =

They are moved automatically, with the same IDs and designs. Your settings become display conditions and the choices of single pages stay. Nothing of version 1 is deleted: Theme Builder › Settings › Return to version 1 gives the templates back, if you ever need to install 1.2.5 again.

= Can I use it with Elementor Pro's theme builder? =

Use only one theme builder for the same part of the site.

== Screenshots ==

1. Theme Builder for the free Elementor: all templates of the site and where they are shown
2. Display conditions: include and exclude rules for every template
3. Design in Elementor with the widgets of the plugin first in the panel
4. Archives with your own post cards: the Posts widget and its card template
5. Template Library: ready-made templates of every type with live previews
6. Websites Import: whole websites, filtered by industry, Free or PRO
7. Choose the colors of a design before the import, with a live preview
8. Theme Builder PRO: WooCommerce shop, popups and mega menus, shown locked in the free plugin

== Changelog ==

= 2.0.1 =
* Fix: after a Websites Import the buttons in sliders, section templates and post cards link to your site, not to the demo site.
* Fix: the Breadcrumbs widget uses the breadcrumbs of Yoast SEO or Rank Math only when they are switched on there, otherwise its own trail.
* Fix: Single and Archive templates for the blog are no longer used for WooCommerce products, the shop and product categories.
* Fix: themes that print their own header or footer through hooks (e.g. Envo One) no longer show it next to the header or footer of the plugin.
* Fix: header, footer and content of the plugin are no longer changed by theme styles (e.g. Royal Elementor Kit, PopularFX).
* Fix: on pages the theme renders, its content container is kept with the header and footer of the plugin.
* Fix: post cards of the Posts widget are no longer boxed again by the theme (e.g. Astra).

= 2.0.0 =
* New: completely rewritten plugin with a new administration.
* New: display conditions with include and exclude rules.
* New: works with any theme, also block themes.
* New: Posts widget, navigation menu, breadcrumbs, post navigation, author box, comments, archive title, site logo, site title, search form, copyright and section template widgets.
* New: Advanced Heading, Post Info, Share Buttons, Table of Contents, Reading Progress, Back to Top and Slider widgets.
* New: Template Library with live previews and one-click import, and Websites Import of whole websites with their header, footer and layouts.
* New: insert sections, templates and demo pages from the library right in the Elementor editor.
* New: choose the colors of a demo before the import (presets, own colors, live preview), optionally linked to the global colors of Elementor.
* New: notice of new templates and websites in the dashboard, "New" badges and industry filters in the library.
* New: 22 dynamic tags for the free Elementor.
* New: sticky, shrinking, hiding and transparent header.
* New: related posts in the Posts widget, search form as an icon, number of results in the archive title, author box on author archives.
* New: Search Results, Post Card and Section templates.
* New: Canvas and Full width page layouts.
* New: the Elementor editor previews templates with real posts.
* Security: the demo import and all actions check permissions and nonces; all output is escaped.
* Templates are no longer public pages and are never indexed.
* Version 1 data is migrated automatically.

= 1.2.5 =
* Tested with WP 6.8
* Security update
