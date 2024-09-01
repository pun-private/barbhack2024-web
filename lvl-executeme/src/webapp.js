const app = require('express')()
const exec = require('child_process').execSync

process.env['PATH'] = '/bin'
process.on('SIGINT', process.exit);
process.on('SIGTERM', process.exit);

function isInBlackist(str, blacklist) {
    return typeof(str) !== 'string' || blacklist.some(char => str.includes(char));
}

app.get('/', async (req, res) => {
    res.set('Content-Type', 'text/plain').send(require('fs').readFileSync(__filename));
})

app.get('/taunt', async (req, res) => {
    try {
        const player = req.query.player || 'anon'

        if (isInBlackist(player, "'&#;,`:%|*?~^-=<>[]{}/ \t".split('')) == true)
            return res.send("Illegal: unauthorized character detected.")

        if (isInBlackist(player, exec("ls /bin").toString().trim().split("\n")) == true)
            return res.send("Illegal: potential attack.")

        if (player.length > 20)
            return res.send("Illegal: player name cannot exceed 20 characters.")

        const quote = exec(`/usr/bin/barbhack This dude _${player}_ thinks he can still solve this challenge at \`date +"%I:%M:%S %p"\`...`)
        res.set('Content-Type', 'text/plain').send(quote)
    } catch(e) {
        res.status(500)
        res.set('Content-Type', 'text/plain').send(e.stdout + "\n" + e.stderr)
    }
});

console.log("Starting webapp...")
app.listen(3000)
