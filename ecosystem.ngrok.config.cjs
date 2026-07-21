module.exports = {
  apps: [
    {
      name: 'marketing-dashboard-ngrok',
      script: './scripts/launchers/start-ngrok-dashboard.command',
      interpreter: 'bash',
      cwd: __dirname,
      autorestart: true,
      watch: false,
      env: {
        PORT: '8000',
      },
      max_restarts: 10,
      restart_delay: 2000,
      time: true,
    },
  ],
};
