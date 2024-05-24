const readline = require('readline');
function promptText(item) {
    return new Promise((resolve, reject) => {
        const rl = readline.createInterface({
            input: process.stdin,
            output: process.stdout
        });

        rl.question(`${item.message} `, (answer) => {
            rl.close();
            resolve(answer);
        });
    });
}

function promptSelect(item) {
    return new Promise((resolve, reject) => {
        const rl = readline.createInterface({
            input: process.stdin,
            output: process.stdout
        });

        const choices = item.choices.map((choice, i) => {
            return `${i + 1}. ${choice}`;
        });

        const message = `${item.message(null, {})}\n  ${choices.join('\n  ')}\n`;

        rl.question(message, (answer) => {
            rl.close();
            resolve(item.choices[parseInt(answer) - 1]);
        });
    });
}

function prompts(items) {
    return new Promise((resolve, reject) => {
        const answers = {};
        let i = 0;

        const next = async () => {
            if (i >= items.length) {
                resolve(answers);
                return;
            }

            const item = items[i];
            let response;

            if (item.type === 'text') {
                response = await promptText(item);
            } else if (item.type === 'select') {
                response = await promptSelect(item);
            }

            answers[item.name] = response;
            i++;

            next();
        };

        next();
    });

}

(async () => {
    const response = await prompts([
        {
            type: 'text',
            name: 'name',
            message: 'What is your name?'
        },
        {
            type: 'select',
            name: 'activity',
            message: (prev, values) => `Hello, ${values.name}! What would you like to do?`,
            choices: [
                'Create a new project',
                'Go on an adventure',
                'Hear interesting facts about Texas'
            ],
            initial: 0
        }
    ]);

    console.log(`${response.name} wants to ${response.activity.toLowerCase()}.`);
})();
