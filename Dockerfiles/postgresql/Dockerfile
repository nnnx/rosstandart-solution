FROM postgres:12.1

COPY postgresql.conf /etc/postgresql/postgresql.conf
COPY init.sql /docker-entrypoint-initdb.d
CMD ["-c", "config_file=/etc/postgresql/postgresql.conf"]
