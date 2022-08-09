import matplotlib.pyplot as plt
import matplotlib.dates as dates
import matplotlib.cm as cm
import pandas as pd
import numpy as np


def render(df, filename):
    fig = plt.figure(figsize=(10, 10))
    ax = fig.add_subplot(projection='3d')
    ax.set_xlabel('Language level')
    ax.set_ylabel('Release date')
    ax.set_zlabel('Value')

    ax.set_xticks(np.arange(0, len(LEVELS), 1), LEVELS)

    ax.yaxis.set_major_formatter(dates.DateFormatter('%Y'))
    ax.yaxis.set_major_locator(dates.YearLocator(base=5))

    ax.plot_trisurf(df['LevelFormat'], df['DateFormat'], df['Value'], cmap=cm.get_cmap('plasma'))

    fig.savefig('resources/results/' + filename + '.pdf', bbox_inches='tight')
    #fig.close()


def render_scoped_and_unscoped(df, name):
    render(df, name)
    render(df[(df['Level'] > 5.5)], name + '_scoped')


def render_package(df, package):
    df = df[df['Package'] == package]
    render_scoped_and_unscoped(df, package)


def render_all(df, name):
    render_scoped_and_unscoped(df, name)


def render_packages(df):
    for package in df['Package'].unique():
        render_package(df, package)


LEVELS = ['5.2', '5.3', '5.4', '5.5', '5.6', '7.0', '7.1', '7.2', '7.3', '8.0', '8.1', '8.2']

df_test = pd.read_csv('resources/results/pandas_test.csv', parse_dates=True)
df_train = pd.read_csv('resources/results/pandas_train.csv', parse_dates=True)
df_all = pd.read_csv('resources/results/pandas.csv', parse_dates=True)

df_test['DateFormat'] = dates.date2num(df_test['Date'])
df_test['LevelFormat'] = [LEVELS.index(str(level)) for level in df_test['Level']]

df_train['DateFormat'] = dates.date2num(df_train['Date'])
df_train['LevelFormat'] = [LEVELS.index(str(level)) for level in df_train['Level']]

df_all['DateFormat'] = dates.date2num(df_all['Date'])
df_all['LevelFormat'] = [LEVELS.index(str(level)) for level in df_all['Level']]

render_all(df_test, 'test')
render_all(df_train, 'train')
render_all(df_all, 'all')

render_packages(df_test)
render_packages(df_train)