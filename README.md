# Oli BETA 2.0.0 – development branch

**Oli** is an *open source PHP framework* designed to help you create your website.  
The framework brings up various development tools such as database and user management, and more.

Want to know more about the project team? [Check out the "Team" section](#team)!

If you like the project, or are interested in it, please consider giving some feedback and leaving a star! ★

## Get started!

*Warning: The BETA is barely documented.*

### Prerequisites

First, you need to make sure you have a working web server set up, with PHP installed. 

As the framework is currently in BETA, please be careful with your installation: Oli is developed using an Apache server with PHP 7.0 and MySQL installed, and no tests has been run using other configations.

### Install

[Download the latest release](https://github.com/OliFramework/Oli/releases/latest), extract the archive and place Oli in your web server file directory.  
The last thing you'll have to do is the config! Need help with that? [Here's a more detailed guide to get started](https://github.com/OliFramework/Oli/wiki/Get-started). (**WIP**)

Once the framework set up, you can create and place your pages in the **/content/theme/...** directory as *.php* files.  
You can place your CSS stylesheets and JS scripts in the **/content/theme/assets/...** directory for an easier access through Oli, using its built-in HTML tools.

### Some extras

**You might also want to look into other things that might help you:**  
Want to use a database with your website? Import the [default Oli SQL file](https://github.com/OliFramework/Oli/blob/master/oli.default.sql) in your MySQL database, and update your *config.json* file with the MySQL access infos.  
Need to use the account management feature? Learn about the [official Oli login page](https://github.com/OliFramework/Oli-Login-Page)!

Interesting by things made by the community? [Check out what projects and addons they made](https://github.com/OliFramework/Oli/wiki/Created-by-the-community)!

You have something to add to the framework? Let us know, or learn about [creating your own addon for Oli](#). (**WIP**)  
Don't forget to *share your creations with us*! ♪

## You

Please make sure to read the [Code of Conduct](https://github.com/OliFramework/Oli/blob/master/CODE_OF_CONDUCT.md) before getting involved in the project, either by participating or by contributing.

### Contributing

You have something to add to the framework? You can contribute to the project by developing addons. To do so, learn about [creating your own addon for Oli](#) (**WIP**).

If you want to suggest new features, feel free to [open a new issue](https://github.com/OliFramework/Oli/issues/new) or see the ["Team" section](#team) to contact us.

You can also get involved in the framework developement, if you'd like to. Please read the [Contributing](https://github.com/OliFramework/Oli/blob/master/CONTRIBUTING.md) file first! (**WIP**)

### Help & Support

Have trouble? Need some help with something?  
You can check out the ["Team" section](#team) to contact us directly or [open a new issue](https://github.com/OliFramework/Oli/issues/new).

---

## License

Copyright (C) 2015-2018 Matiboux (Mathieu Guérin)
> This program is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.  
> 
> This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
> See the GNU Affero General Public License for more details.
> 
> You should have received a copy of the GNU Affero General Public License along with this program.  
> If not, see <http://www.gnu.org/licenses/>.

*You'll find a copy of the GNU AGPL v3 license in the **LICENSE** file.*

This license applies to the...
- **/index.php** file
- **/load.php** file
- Files in **/includes/...** – and in sub-directories

This license does not apply to the...
- Website configuration files: **/config.json**, **/config.global.json**, and **/app.json**
- Git configuration files such as **/.gitignore**
- **.htaccess** files
- **/oli.default.sql** file (licensed under the MIT license)
- Admin panel: files in **/includes/admin/...** file (which is licensed under the MIT license)

This license does not apply either to your own files and other libraries:
- The **/addons/** folder should contain files of your website. We believe your website and its files are not derivative work as long as they only use the framework as-is without modifying it, and thus files in **/content/...** (and in sub-directories) **do not inherit the AGPL license**.  
Files in **/content/...** are files you should own or have the right to use. You're responsible for how you use them.
- The **/addons/** folder consists of an "aggregate" of libraries, and thus files in **/addons/...** (and in sub-directories) **do not inherit the AGPL license**.

ℹ️ The website pages (files in **/content/...**), as they are in this repository, are **not licensed**. They're here for the example and you're free to edit them and re-use them.

*Paths beginning with **/** are relative to the main directory of this repository.*

---

## Team

**Creator & Developer**: Matiboux (Mathieu Guérin)  
Want to get in touch with me? Here's how:
 - **Email**: [matiboux@gmail.com](mailto:matiboux@gmail.com)
 - **Github**: [@matiboux](https://github.com/matiboux)
 - **Twitter**: [@Matiboux](https://twitter.com/Matiboux)
 - **Telegram**: [@Matiboux](https://t.me/Matiboux)
 
### Contributors

See who also [contributed to this project](https://github.com/OliFramework/Oli/blob/master/CONTRIBUTORS.md)!

### Thanks credits

[@SeeMyPing](https://twitter.com/SeeMyPing), who suggested "Oli" as the framework name.

[@Elionatrox](https://twitter.com/Elionatrox), who helped me for a while..

---

**Community and feedbacks are everything! Help is always appreciated! <3**