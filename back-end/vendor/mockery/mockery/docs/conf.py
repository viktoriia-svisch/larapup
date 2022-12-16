import sys
import os
extensions = [
    'sphinx.ext.todo',
]
templates_path = ['_templates']
source_suffix = '.rst'
master_doc = 'index'
project = u'Mockery Docs'
copyright = u'Pádraic Brady, Dave Marshall and contributors'
version = '1.0'
release = '1.0-alpha'
exclude_patterns = ['_build']
pygments_style = 'sphinx'
html_theme = 'default'
html_static_path = ['_static']
htmlhelp_basename = 'MockeryDocsdoc'
latex_elements = {
}
latex_documents = [
  ('index', 'MockeryDocs.tex', u'Mockery Docs Documentation',
   u'Pádraic Brady, Dave Marshall, Wouter, Graham Campbell', 'manual'),
]
man_pages = [
    ('index', 'mockerydocs', u'Mockery Docs Documentation',
     [u'Pádraic Brady, Dave Marshall, Wouter, Graham Campbell'], 1)
]
texinfo_documents = [
  ('index', 'MockeryDocs', u'Mockery Docs Documentation',
   u'Pádraic Brady, Dave Marshall, Wouter, Graham Campbell', 'MockeryDocs', 'One line description of project.',
   'Miscellaneous'),
]
on_rtd = os.environ.get('READTHEDOCS', None) == 'True'
if not on_rtd:  # only import and set the theme if we're building docs locally
    import sphinx_rtd_theme
    html_theme = 'sphinx_rtd_theme'
    html_theme_path = [sphinx_rtd_theme.get_html_theme_path()]
    print sphinx_rtd_theme.get_html_theme_path()
from sphinx.highlighting import lexers
from pygments.lexers.web import PhpLexer
lexers['php'] = PhpLexer(startinline=True)
lexers['php-annotations'] = PhpLexer(startinline=True)
