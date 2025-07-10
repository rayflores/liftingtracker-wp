/**
 * WordPress Scripts Configuration
 *
 * This file extends the default @wordpress/scripts webpack configuration
 * to customize the build process for the LiftingTracker Pro theme.
 */

const defaultConfig = require("@wordpress/scripts/config/webpack.config");
const path = require("path");

module.exports = {
  ...defaultConfig,
  entry: {
    main: path.resolve(process.cwd(), "src/js", "main.js"),
  },
  output: {
    ...defaultConfig.output,
    path: path.resolve(process.cwd(), "build"),
    filename: "[name].js",
  },
  resolve: {
    ...defaultConfig.resolve,
    alias: {
      ...defaultConfig.resolve.alias,
      "@": path.resolve(__dirname, "src"),
      "@js": path.resolve(__dirname, "src/js"),
      "@scss": path.resolve(__dirname, "src/scss"),
    },
  },
  // Development server configuration
  devServer: {
    ...defaultConfig.devServer,
    hot: true,
    liveReload: true,
    watchFiles: ["src/**/*"],
  },
};

