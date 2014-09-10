# Selendroid Driver

A Selendroid Driver for Mink.

## Install with composer (currently using require-dev)
```json
"require-dev": {
    "kasper-agg/mink-selendroid-driver": "dev-master"
},

"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/kasper-agg/minkSelendroidDriver.git"
    }
],
```

## Add the Extension

Note: you must manually create an AVD and make sure your settings match. Currently a named screenSize like "WXGA800" causes Java errors, so change your AVD to an explicit size like "480x800".
```yml
  Selendroid\BehatExtension\Extension:
    selendroid:
      capabilities: {browserName: "android", screenSize: "480x800", emulator: true, platformVersion: "19"}
```
