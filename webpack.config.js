const path = require('path');
const CopyPlugin = require('copy-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');


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
          test: /\.s[ac]ss$/i,
          use: [
            MiniCssExtractPlugin.loader,
            { loader: 'css-loader' },
            { loader: 'sass-loader', options: { sourceMap: true } },
          ]
        }
      ]
    },
    plugins: [  new MiniCssExtractPlugin({ filename: 'app.css' }),  ]
};

const MixyConfiguration = {
    entry: './src/mixy/app.js',
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
    plugins: [ new MiniCssExtractPlugin({ filename: 'mixy.css' }), ],
    devtool: 'inline-source-map',
}

module.exports = [
    AppConfiguration,
    MixyConfiguration
].concat(require('./kiss/webpack.config'));