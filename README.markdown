coGenerador is really simple "catalog generator" original designed for generate a catalog of pdf files. And used for more "cool" tricks

use
---
 generador.php fileToGenerate routeToTemplate baseDir fileTypeToCatalog maxRecursive

Tecnical Stuff
--------------
* single file (yes, is true, all source code are contend in one file!!!!)
* templating system (yes, are "much" files) for complete skineable
* uses [coSimpleTemplate] [coSimpleTemplateGist] a simple and embeddable template motor
[coSimpleTemplateGist]:https://gist.github.com/1356936
* really fast (is true, generate a catalog of 200 archives in lees than 1 second)
* manage correctly the multi-anidation structures
* use (if are avalaible) PHP Tidy And XmlBeautiffier (yes from PEAR)
*

know bugs-limitations
---------------------
* return warning if your not count whith XmlBeautiffier
* in "some cases" fail in include "remote" files in the manifest (url Wrapper error)
* is a "ugly" single file

Work in progress
----------------

* PEAR package
* More Templates (Images for example)
* Pretty Doc
* Best README file.


knowldes users
--------------
[Portal de transparencia del Colegio de Bachilleres del Estado de Tlaxcala][cobatrans]
[cobatrans]:http://transparencia.cobatlaxcala.edu.mx


