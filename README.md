# tnfs-php

This is a class for using the [TNFS protocol](https://github.com/FujiNetWIFI/spectranet/blob/master/tnfs/tnfs-protocol.md) with PHP. An example script is included.

## Docker

To run the example script with Docker, please execute:

```
docker build -t tnfs .
docker run -ti --rm -p 80:8080 tnfs
```

And then open http://localhost:8080 in your browser.
