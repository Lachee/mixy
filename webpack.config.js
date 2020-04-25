const webpack = require('webpack');
const path = require('path');
const CopyPlugin = require('copy-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const MonacoWebpackPlugin = require("monaco-editor-webpack-plugin");
const DeclarationBundlerPlugin = require('declaration-bundler-webpack-plugin');
const WrapperPlugin = require('wrapper-webpack-plugin');
const ExtractTextPlugin = require("extract-text-webpack-plugin");

const Externals = {  
  'mixy/mixy': 'mixlib',
  '../mixy/mixy': 'mixlib',
  'monaco/index': 'monacolib',
  '../monaco/index': 'monacolib',
}

const JSRule = {
  test: /\.m?js$/,
  exclude: /node_modules/,
  use:  {
    loader: 'babel-loader', 
    options: {                
      presets: ['@babel/preset-env'],
      plugins: [
        "@babel/plugin-proposal-class-properties",
        "@babel/plugin-proposal-private-methods",
        '@babel/plugin-transform-runtime'
      ]
    },
  }
};

const AppConfiguration = {
    entry: './src/app/app.js',
    output: {
        filename: 'app.js',
        chunkFilename: 'bundle.[name].js',
        path: path.resolve(__dirname, './public/dist'),
        publicPath: '/dist/',
        library: 'app',
    },
    module: {
      rules: [
        JSRule,
        {
          test: /\.s?[ac]ss$/i,
          exclude: /view.*/,
          use: [
            MiniCssExtractPlugin.loader,
            { loader: 'css-loader' },
            { loader: 'sass-loader', options: { sourceMap: true } },
          ]
        },
        {
          test: /view.*\.s?[ac]ss$/i,
          use: [ 'style-loader', 'css-loader', 'sass-loader' ],
        }
      ]
    },
    plugins: [  new MiniCssExtractPlugin({ filename: 'app.css' }) ],
    externals: Externals
};

const MixyConfiguration = {
    entry: './src/mixy/mixy.js',
    output: {
        filename: 'mixy.js',
        chunkFilename: 'vendor.[name].js',
        path: path.resolve(__dirname, 'public/dist'),
        publicPath: '/dist/',
        library: 'mixlib',
    },
    module: {
        rules: [
          JSRule,
          {
            test: /\.s?[ac]ss$/i,
            use: [ 'style-loader', 'css-loader', 'sass-loader' ],
          },
      ]
    },
    plugins: [  ],
    externals: Externals
}

const MonacoConfiguration = {
	entry: './src/monaco/index.js',
  output: {
    chunkFilename: '[name].bundle.js',
    path: path.resolve(__dirname, 'public/dist/monaco'),
    publicPath: '/dist/monaco/',
    library: "monacolib"
  },
	module: {
		rules: [
      {
        test: /\.d\.ts$/,
        use: ['raw-loader']
      },
      JSRule, 
      {
        test: /\.css$/i,
        use: [ 'style-loader', 'css-loader', 'sass-loader', ],
      }, {
        test: /\.ttf$/,
        use: ['file-loader']
      }
    ]
  },
  plugins: [
		new MonacoWebpackPlugin({
			languages: ["typescript", "javascript", "css", "html", "json"],
		})
  ],
  externals: Externals
}

/*
const TweenConfiguration = {
  entry: '@tweenjs/tween.js',
  output: {    
    filename: 'tweenjs.js',
    path: path.resolve(__dirname, 'public/dist/'),
    publicPath: '/dist/',
    library: 'createjs',
    libraryExport: 'default'
  },
}
*/


module.exports = [
    AppConfiguration,
    MixyConfiguration,
    MonacoConfiguration,
].concat(require('./kiss/webpack.config'));