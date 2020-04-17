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

module.exports = [
    AppConfiguration
];//.concat(require('./kiss/webpack.config'));