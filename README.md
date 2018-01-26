# Spleef
Spleef using https://github.com/thebigsmileXD/gameapi
## Clarification
This plugin is just a proof of concept of making things easier for developers using OOP

Though just being a concept, you could actually play this game. Go test it on [wolvesfortress.de:19133 (click to add to Minecraft)](https://server.wolvesfortress.de/quickadd.php?game=1) if you like to.

Setup is really easy. The world is automatically generated, called 'spleef'. To replace the map with another map, go to /plugins/Spleef/worlds and replace your 'spleef' data with your worlds data (you should keep the level.dat though)

Joining is done by using signs, but you can add any event for joining that you'd like - in JoinEventListener.php

Sign setup:
```
L1: [Spleef]
L2: mapname
L3: 
L4: 
```
Then, click on it, and you are set.

Breakable blocks are snow blocks (not top layers), and they will drop snow balls, so you can shoot off opponents.

**You need to set up DEVirion and install the [gameapi](https://github.com/thebigsmileXD/gameapi) virion properly if you are running from source!**
(you could also turn this repository into a poggit project instead and use a compiled phar)
**Please search up how this is done yourself!**

## Disclaimer
This is a proof of concept repository. Please do **not** open issues about gameplay, setup issues, or similar. This repository is here to help people to understand how [gameapi](https://github.com/thebigsmileXD/gameapi) works with OOP, and how using a library can make programming alot easier. This repository is just for learning-by-reading and learning-by-doing purposes. You can modify the code by your needs and wills (see [LICENSE](https://github.com/thebigsmileXD/Spleef/blob/master/LICENSE)).
