{ pkgs, lib, config, inputs, ... }:

{
  dotenv.disableHint = true;

  # https://devenv.sh/basics/
  env.GREET = "devenv";

  # https://devenv.sh/packages/
  packages = [ pkgs.git ];

  # https://devenv.sh/languages/
  languages.php.enable = true;
  languages.php.version = "8.3";
  languages.php.extensions = [ "apcu" "intl" "opcache" "zip" "redis" "pdo_pgsql" "sysvsem" "xdebug" ];
  languages.php.disableExtensions = [ "soap" ];
  languages.php.ini = builtins.readFile ./frankenphp/conf.d/10-app.ini;

  languages.javascript.enable = true;
  languages.javascript.corepack.enable = true;

  # https://devenv.sh/processes/
  # processes.cargo-watch.exec = "cargo-watch";

  # https://devenv.sh/services/
  # services.postgres.enable = true;

  # https://devenv.sh/scripts/
  scripts.hello.exec = ''
    echo hello from $GREET
  '';

  enterShell = ''
    hello
    git --version
  '';

  # https://devenv.sh/tests/
  enterTest = ''
    echo "Running tests"
    git --version | grep --color=auto "${pkgs.git.version}"
  '';

  # https://devenv.sh/pre-commit-hooks/
  pre-commit.hooks.shellcheck.enable = true;
  pre-commit.hooks.lint = {
    enable = true;
    entry = "composer run-script lint";
    pass_filenames = false;
  };

  # See full reference at https://devenv.sh/reference/options/
}
