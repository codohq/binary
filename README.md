# Codo Binary

```bash
sudo mkdir -p /opt/codohq

sudo chgrp -R docker /opt/codohq
sudo chmod -R g+rwx /opt/codohq

git clone git@github.com:codohq/binary.git /opt/codohq/binary

docker run --rm --interactive --tty --volume /opt/codohq/binary:/app composer install

sudo ln -sf /opt/codohq/binary/bin/run.sh /usr/local/bin/codo
```
