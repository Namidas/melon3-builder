<img width="653" height="475" alt="project-select" src="https://github.com/user-attachments/assets/80aa7304-2341-491f-a533-393b9ac13dd3" />

*melon3* framework project builder, although, technically, it can parse/build/package just anything.

##Introduction
Originally I had two separate "problems" that I needed to tackle on:

1. *melon3* was growing real fast, and even I was losing track of everything on it, so I needed to somehow *thoroughly document it*.
2. The way I organize my code (specially framework related and/or reusable code) usually means a project takes dependencies from lots of different paths, sometimes even different drives, and it meant slightly problematic situations when needing to publish changes or whatever...so I needed an automated way to pick everything up and somehow *pack it*.

I know there already are existing tools for each of those tasks, but I felt like doing a little experiment and...hell, why not? Let's just reinvent the wheel and put those 2 "things" into a single tool.

And so this "builder" started to take form in my head, I knew I needed to "somehow pickup all the paths" to "pack" a project, but also parse source code to generate documentation (actually, the idea was never to make it as automatic as it sounds, but rather have some sort of meta language inside the code itself, parse it, generate the docs, and clean the code).

At first I thought of using a different tech (keep in mind, that I was thinking of "doing this" with PHP projects) to accomplish this, like Python (that kinda feels like the go-to tech to do something like this) but then, after putting much thought to it, I realized/decided that actually there is a tech that I'm already extremely proficient with and could get the job done...PHP itself.

I was quite certain that PHP had configurations for changing opening and closing tags, so the idea is I would just use raw PHP to do whatever (even inside PHP scripts) but tagged with a different specific tag that only this builder would recognize...of course, that wasn't the case, you can't change/config those tags, but you know which other tech I have over 20 years of experience with and I knew for sure that would allow me to config it's tags (just to not visually break code)?...that's right, Smarty, the template engine for PHP.

So that's that...essentially this *builder is a melon3 project itself* but comes packed with it's own dist since it's used to actually build the framework itself. It's pure raw PHP that uses Smarty as it's main engine to parse files (which, if you know Smarty, you know the possibilities are essentially endless), packed with default custom extensions aimed specifically to generating documentation.

## About this repository
You have 2 root foolders, `0.1` and `melon_custom`, the latter is of course a pre-built *melon3* and `0.1` holds the actual "thing" (yeah, that's just how I'm used to organize my code, I'm old school, get over it).

Inside that folder you'll find 2 different folders, `server` and `app`, `server` has the builder itself all the PHP code, templates, etc, and `app` contains a Vue3 app that acts as a frontend/GUI for the server app (where screenshots are coming from).

<img width="1020" height="485" alt="project-details" src="https://github.com/user-attachments/assets/61f95cb5-cd3b-4225-a288-32d5afabeb3c" />
