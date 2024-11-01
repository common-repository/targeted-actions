const gulp = require('gulp');
const sass = require('gulp-sass');
const browserSync = require('browser-sync').create();//compile scss into css

function style() {
    return gulp.src('assets/scss/index.scss')
    .pipe(sass().on('error',sass.logError))
    .pipe(gulp.dest('assets/css'))
    .pipe(browserSync.stream());
}

exports.style = style;

function modal() {
    return gulp.src('assets/scss/modal/modal.scss')
    .pipe(sass().on('error',sass.logError))
    .pipe(gulp.dest('assets/css'))
    .pipe(browserSync.stream());
}

exports.modal = modal;