const path = require('path');
const CopyPlugin = require('copy-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const MonacoWebpackPlugin = require("monaco-editor-webpack-plugin");
const DeclarationBundlerPlugin = require('declaration-bundler-webpack-plugin');
//const BabelProposalClassProperties = require('@babel/plugin-proposal-class-properties');

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
        {
          test: /\.s?[ac]ss$/i,
          use: [
            MiniCssExtractPlugin.loader,
            { loader: 'css-loader' },
            { loader: 'sass-loader', options: { sourceMap: true } },
          ]
        }
      ]
    },
    plugins: [  new MiniCssExtractPlugin({ filename: 'app.css' }) ],
    externals: {
      'mixy/mixy': 'mixy'
    }
};

const MixyConfiguration = {
    entry: './src/mixy/mixy.js',
    output: {
        filename: 'mixy.js',
        chunkFilename: 'vendor.[name].js',
        path: path.resolve(__dirname, 'public/dist'),
        publicPath: '/dist/',
        library: 'mixy',
    },
    module: {
        rules: [
          {
            test: /\.m?js$/,
            use:  {
              loader: 'babel-loader', 
              options: {
                plugins: [
                  "@babel/plugin-proposal-class-properties",
                  "@babel/plugin-proposal-private-methods"
                ]
              },
            }
          },
          {
            test: /\.tsx?$/,
            use: 'ts-loader',
            exclude: /node_modules/,
          },      
          {
            test: /\.css$/i,
            use: [
                'style-loader',
                'css-loader',
                'sass-loader',
            ],
          },
          {
            test: /\.s[ac]ss$/i,
            use: [
              MiniCssExtractPlugin.loader,
              { loader: 'css-loader' },
              { loader: 'sass-loader', options: { sourceMap: true } },
            ]
          }
      ]
    },
    plugins: [ 
      new MiniCssExtractPlugin({ filename: 'mixy.css' }),       
      new DeclarationBundlerPlugin({
        moduleName:'module.Mixy',
        out:'index.d.ts',
      }) 
    ],
    devtool: 'source-map',
}

const MonacoConfiguration = {
	entry: './src/monaco/index.js',
  output: {
    chunkFilename: '[name].bundle.js',
    path: path.resolve(__dirname, 'public/dist'),
    publicPath: '/dist/',
  },
	module: {
		rules: [{
			test: /\.css$/,
			use: ['style-loader', 'css-loader']
		}, {
			test: /\.ttf$/,
			use: ['file-loader']
		}]
  },
  plugins: [
		new MonacoWebpackPlugin({
			languages: ["typescript", "javascript", "css", "html"],
		})
  ],
  externals: {
    '../mixy/mixy': 'mixy'
  }
}

module.exports = [ MixyConfiguration, MonacoConfiguration ];
return; 

module.exports = [
    AppConfiguration,
    MixyConfiguration,
    MonacoConfiguration
].concat(require('./kiss/webpack.config'));