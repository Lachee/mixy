const path = require('path');
const CopyPlugin = require('copy-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');


const KissConfiguration = {
    entry: './kiss/src/kiss/kiss.js',
    output: {
        filename: 'kiss.js',
        chunkFilename: 'bundle.[name].js',
        path: path.resolve(__dirname, '../public/dist'),
        publicPath: '/dist/',
        library: 'kiss',
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
    plugins: [
      new MiniCssExtractPlugin({ filename: 'kiss.css' }),
    ]
};

module.exports = [
    KissConfiguration
];