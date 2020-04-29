from crate.theme.rtd.conf.dbal import *

from sphinx.highlighting import lexers
from pygments.lexers.web import PhpLexer
lexers["php"] = PhpLexer(startinline=True, linenos=1)
lexers["php-annotations"] = PhpLexer(startinline=True, linenos=1)
# primary_domain = "php"

source_suffix = '.rst'
html_theme_options.update({
    'canonical_url_path': 'docs/dbal/',
    'tracking_project': 'crate-dbal',
})

site_url = 'https://crate.io/docs/dbal/en/latest/'
extensions = ['sphinx_sitemap']
