Note: The Projects directory needs to be owned by the root group in order to give access to nginx and www-data in development environment (chmod and setafcl).
Note: Running "cat .env | base64 > encrypted.env" is neccessary for .env file's updates to take effect in production.
Note: Please run `git config core.filemode false`