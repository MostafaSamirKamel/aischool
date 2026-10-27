FROM nginx:alpine

# Install envsubst for dynamic PORT substitution
RUN apk add --no-cache gettext

COPY AI_School_Student_Frontend/nginx.conf.template /etc/nginx/templates/default.conf.template
COPY AI_School_Student_Frontend/ /usr/share/nginx/html/

# Remove non-runtime assets from html directory
RUN rm -f /usr/share/nginx/html/nginx.conf.template /usr/share/nginx/html/Dockerfile

ENV PORT=8080

EXPOSE 8080

CMD ["/bin/sh", "-c", "envsubst '${PORT}' < /etc/nginx/templates/default.conf.template > /etc/nginx/conf.d/default.conf && exec nginx -g 'daemon off;'"]
