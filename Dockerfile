FROM phusion/baseimage

# Install dependency packages
RUN apt-get clean && apt-get update && apt-get install -y apt-utils python3 python3-dev python3-pip build-essential \
    libpq-dev

WORKDIR /srv
COPY requirements.txt /srv/requirements.txt
RUN pip3 install --upgrade pip
RUN pip3 install -r requirements.txt
COPY . /srv

EXPOSE 5000
