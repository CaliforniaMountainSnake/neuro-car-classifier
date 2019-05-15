FROM python:3.6.8-slim

# Install the base soft:
RUN apt-get update && apt-get install -y \
    wget \
    curl \
    zip \
    nano \
    htop

# Set timezone:
RUN ln -snf /usr/share/zoneinfo/$CONTAINER_TIMEZONE_PYTHON /etc/localtime && echo $CONTAINER_TIMEZONE_PYTHON > /etc/timezone

# Install python packages:
ADD ./.docker_build_configs/python/requirements.txt /var/requirements.txt
RUN pip install -r /var/requirements.txt

WORKDIR /var/www/app
CMD python flask_app.py
