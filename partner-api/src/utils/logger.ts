import winston from 'winston';
import path from 'path';

// Define log levels
const levels = {
  error: 0,
  warn: 1,
  info: 2,
  http: 3,
  debug: 4,
};

// Define colors for each level
const colors = {
  error: 'red',
  warn: 'yellow',
  info: 'green',
  http: 'magenta',
  debug: 'white',
};

// Tell winston that you want to link the colors
winston.addColors(colors);

// Choose the aspect of your log customizing the log format.
const format = winston.format.combine(
  winston.format.timestamp({ format: 'YYYY-MM-DD HH:mm:ss:ms' }),
  winston.format.errors({ stack: true }),
  winston.format.colorize({ all: true }),
  winston.format.printf((info) => {
    const { timestamp, level, message, stack } = info;
    if (stack) {
      return `${timestamp} ${level}: ${message}\n${stack}`;
    }
    return `${timestamp} ${level}: ${message}`;
  })
);

// Define which transports the logger must use to print out messages.
const transports: winston.transport[] = [
  // Allow console logging
  new winston.transports.Console({
    format,
  }),
];

// Add file transport in production
if (process.env.NODE_ENV === 'production') {
  // Ensure logs directory exists
  const logDir = path.dirname(process.env.LOG_FILE || 'logs/partner-api.log');
  
  transports.push(
    // Allow to print all the error level messages inside the error.log file
    new winston.transports.File({
      filename: path.join(logDir, 'error.log'),
      level: 'error',
      format: winston.format.combine(
        winston.format.timestamp(),
        winston.format.errors({ stack: true }),
        winston.format.json()
      ),
    }),
    // Allow to print all the messages inside the all.log file
    new winston.transports.File({
      filename: process.env.LOG_FILE || path.join(logDir, 'partner-api.log'),
      format: winston.format.combine(
        winston.format.timestamp(),
        winston.format.errors({ stack: true }),
        winston.format.json()
      ),
    })
  );
}

// Create the logger instance that has to be exported
// and used to log messages.
export const logger = winston.createLogger({
  level: process.env.LOG_LEVEL || 'info',
  levels,
  transports,
  // Do not exit on handled exceptions
  exitOnError: false,
});

// Create a stream object with a 'write' function that will be used by `morgan`
export const loggerStream = {
  write: (message: string) => {
    logger.http(message.trim());
  },
};

export default logger;