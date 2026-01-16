const prettier = require('prettier');
const fs = require('fs').promises;
const path = require('path');

async function getExtensions() {
    const arrayWithDefaultExtensions = [
        ".css",
        ".graphql",
        ".js",
        ".json",
        ".jsx",
        ".less",
        ".sass",
        ".scss",
        ".ts",
        ".tsx",
        ".vue",
        ".yaml",
    ];
    if (prettier.getSupportInfo) {
        try {
            const supportInfo = await prettier.getSupportInfo();
            return supportInfo.languages
                .map(l => l.extensions)
                .reduce((accumulator, currentValue) => accumulator.concat(currentValue), []);
        } catch (error) {
            console.error("Error fetching support info:", error);
            return arrayWithDefaultExtensions;
        }
    }
    return arrayWithDefaultExtensions;
}

class PrettierPlugin {
    constructor(options) {
        this.options = options || {};
        this.encoding = this.options.encoding || "utf-8";
        this.extensions = this.options.extensions || [];
        this.configFile = this.options.configFile || `${process.cwd()}/.prettierrc`;
        this.prettierOptions = {};
    }

    async init() {
        try {
            const configOptions = await prettier.resolveConfig(this.configFile) || {};
            this.prettierOptions = { ...configOptions, ...this.options };
        } catch (error) {
            console.error("Error resolving Prettier config:", error);
            this.prettierOptions = this.options;
        }
    }

    apply(compiler) {
        compiler.hooks.emit.tapAsync('Prettier', async (compilation, callback) => {
            await this.init();

            const promises = [];
            compilation.fileDependencies.forEach(filepath => {
                if (this.extensions.indexOf(path.extname(filepath)) === -1) return;
                if (/node_modules/.test(filepath)) return;

                promises.push(this.processFile(filepath));
            });

            try {
                await Promise.all(promises);
                callback();
            } catch (err) {
                callback(err);
            }
        });
    }

    async processFile(filepath) {
        try {
            const source = await fs.readFile(filepath, this.encoding);
            const prettierSource = await prettier.format(source, { ...this.prettierOptions, filepath });

            if (prettierSource !== source) {
                await fs.writeFile(filepath, prettierSource, this.encoding);
            }
        } catch (err) {
            console.error(`Error processing file ${filepath}:`, err);
        }
    }
}

module.exports = PrettierPlugin;

getExtensions().then(extensions => {
    module.exports.defaultExtensions = extensions;
});