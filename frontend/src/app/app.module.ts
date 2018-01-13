import {BrowserModule} from '@angular/platform-browser';
import {HttpModule} from '@angular/http';
import {NgModule} from '@angular/core';
import {FormsModule, ReactiveFormsModule} from '@angular/forms';
import {
MatButtonModule, MatCheckboxModule, MatFormFieldModule, MatInputModule,
MatSnackBarModule, MatTooltipModule, MatToolbarModule, MatIconModule,
MatListModule, MatDialogModule
} from '@angular/material';
import {BrowserAnimationsModule} from '@angular/platform-browser/animations';

import {routing, appRoutingProviders} from './app.routing';
import {AppComponent} from './app.component';
import {LoginComponent, SnackBarComponentExample, SnackBarComponentExampleError} from './components/login.component';
import {RegisterComponent} from './components/register.component';
import {DefaultComponent} from './components/default.component';
import {UserEditComponent} from './components/user.edit.component';
import {TaskNewComponent} from './components/task.new.component';
import {TaskEditComponent, DialogContentExample} from './components/task.edit.component';
import {GenerateDatePipe} from './pipes/generate.date.pipe';

@NgModule({
    declarations: [
        AppComponent,
        LoginComponent,
        RegisterComponent,
        DefaultComponent,
        UserEditComponent,
        TaskNewComponent,
        TaskEditComponent,
        GenerateDatePipe,
        SnackBarComponentExample,
        SnackBarComponentExampleError,
        DialogContentExample,
    ],
    imports: [
        routing,
        BrowserModule,
        HttpModule,
        FormsModule,
        MatButtonModule,
        MatCheckboxModule,
        MatFormFieldModule,
        MatInputModule,
        MatSnackBarModule,
        MatTooltipModule,
        MatToolbarModule,
        MatIconModule,
        MatListModule,
        MatDialogModule,
        BrowserAnimationsModule,
        ReactiveFormsModule
    ],
    entryComponents: [
        SnackBarComponentExample,
        SnackBarComponentExampleError,
        DialogContentExample,
    ],
    providers: [
        appRoutingProviders
    ],
    bootstrap: [AppComponent]
})
export class AppModule {}
