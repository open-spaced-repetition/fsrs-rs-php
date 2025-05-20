# To learn more about how to use Nix to configure your environment
# see: https://developers.google.com/idx/guides/customize-idx-env
{ pkgs, ... }: {
  # Which nixpkgs channel to use.
  channel = "stable-24.11"; # or "unstable"

  # Use https://search.nixos.org/packages to find packages
  packages = [
    pkgs.rustup
    pkgs.clang-tools
    pkgs.clang
    pkgs.libclang
    pkgs.php81.unwrapped # Provides the PHP executable
    pkgs.php81.unwrapped.dev
    pkgs.buildPackages.stdenv.cc.cc.lib # Provides standard C headers like stdlib.h
    pkgs.pkg-config # often useful for build scripts
    pkgs.fish
    pkgs.git
  ];

  # Sets environment variables in the workspace
  env = {
    PATH = pkgs.lib.mkForce "${pkgs.php81.unwrapped}/bin:${pkgs.php81.unwrapped.dev}/bin:${pkgs.clang}/bin:$PATH";
    PHP_CONFIG = "${pkgs.php81.unwrapped.dev}/bin/php-config";
    PHP = "${pkgs.php81.unwrapped}/bin/php";
    LIBCLANG_PATH = pkgs.lib.mkForce "${pkgs.libclang.lib}/lib";

    # Explicitly add include paths for bindgen and cc crate
    # This tells clang (used by bindgen) and the cc crate where to find system headers.
    BINDGEN_EXTRA_CLANG_ARGS = pkgs.lib.mkForce (
      # Includes from the C standard library (glibc)
      "-I${pkgs.glibc.dev}/include " +
      # Includes from the selected PHP version
      "-I${pkgs.php81.unwrapped.dev}/include/php " +
      "-I${pkgs.php81.unwrapped.dev}/include/php/main " +
      "-I${pkgs.php81.unwrapped.dev}/include/php/TSRM " +
      "-I${pkgs.php81.unwrapped.dev}/include/php/Zend "
      # You might need to add other specific paths from php-config --includes if they are missing
    );

    # Setting C_INCLUDE_PATH might also help the `cc` crate directly
    C_INCLUDE_PATH = pkgs.lib.mkForce "${pkgs.glibc.dev}/include";

    # Ensure clang from nixpkgs is used by cc crate and bindgen
    CC = "${pkgs.clang}/bin/clang";
  };

  idx = {
    # Search for the extensions you want on https://open-vsx.org/ and use "publisher.id"
    extensions = [
      # "vscodevim.vim"
    ];

    # Enable previews
    previews = {
      enable = true;
      previews = {
        # web = {
        #   # Example: run "npm run dev" with PORT set to IDX's defined port for previews,
        #   # and show it in IDX's web preview panel
        #   command = ["npm" "run" "dev"];
        #   manager = "web";
        #   env = {
        #     # Environment variables to set for your server
        #     PORT = "$PORT";
        #   };
        # };
      };
    };

    # Workspace lifecycle hooks
    workspace = {
      # Runs when a workspace is first created
      onCreate = {
        # Example: install JS dependencies from NPM
        # npm-install = "npm install";
        "setup" = "rustup default stable";
      };
      # Runs when the workspace is (re)started
      onStart = {
        # Example: start a background task to watch and re-build backend code
        # watch-backend = "npm run watch-backend";
      };
    };
  };
}
